<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

class MY_Router extends CI_Router
{
    const MAIN = 'main';
    const ROUTE = 'route';
    const NAME = 'name';
    const URL = 'url';
    const REPLACE = 'replace';
    const REG_REPLACE = 'reg_replace';
    const REPLACE_KEY = 'key';
    const REPLACE_VALUE = 'value';
    const REDIRECT = 'redirect';

    /**
     * Search in Array
     *
     * @param array $arr
     * @param string $key
     * @param string $value
     * @return int index of element
     */
    function _search_by_key(&$arr, $key, $value)
    {
        $ret = false;
        foreach($arr as $k => $v)
        {
            if(!is_int($k)) continue;

            if(is_array($v))
            {
                if($v[$key] == $value)
                {
                    $ret = $k;
                    break;
                }
            }
        }
        return $ret;
    }

    function _set_routing($module = '')
    {

                // Are query strings enabled in the config file?
		// If so, we're done since segment based URIs are not used with query strings.
		if ($this->config->item('enable_query_strings') === TRUE AND isset($_GET[$this->config->item('controller_trigger')]))
		{
			$this->set_class(trim($this->uri->_filter_uri($_GET[$this->config->item('controller_trigger')])));

			if (isset($_GET[$this->config->item('function_trigger')]))
			{
				$this->set_method(trim($this->uri->_filter_uri($_GET[$this->config->item('function_trigger')])));
			}

			return;
		}

		// Load the routes.php file.
		@include(APPPATH.'config/routes'.EXT);
		$this->routes = ( ! isset($route) OR ! is_array($route)) ? array() : $route;
        
		unset($route);

		// Set the default controller so we can display it in the event
		// the URI doesn't correlated to a valid controller.
		$this->default_controller = ( ! isset($this->routes['default_controller']) OR $this->routes['default_controller'] == '') ? FALSE : strtolower($this->routes['default_controller']);

		// Fetch the complete URI string
		$this->uri->_fetch_uri_string();

		// Is there a URI string? If not, the default controller specified in the "routes" file will be shown.
		if ($this->uri->uri_string == '')
		{
			if ($this->default_controller === FALSE)
			{
				show_error("Unable to determine what should be displayed. A default route has not been specified in the routing file.");
			}

			if (strpos($this->default_controller, '/') !== FALSE)
			{
				$x = explode('/', $this->default_controller);

				$this->set_class(end($x));
				$this->set_method('index');
				$this->_set_request($x);
			}
			else
			{
				$this->set_class($this->default_controller);
				$this->set_method('index');
				$this->_set_request(array($this->default_controller, 'index'));
			}

			// re-index the routed segments array so it starts with 1 rather than 0
			$this->uri->_reindex_segments();

			log_message('debug', "No URI present. Default controller set.");
			return;
		}
		unset($this->routes['default_controller']);

		// Do we need to remove the URL suffix?
		$this->uri->_remove_url_suffix();

		// Compile the segments into an array
		$this->uri->_explode_segments();

		// Parse any custom routing that may exist
		$this->_parse_routes();

		// Re-index the segment array so that it starts with 1 rather than 0
		$this->uri->_reindex_segments();
    }


    /**
     *  Parse Routes
     *
     * This function matches any routes that may exist in
     * the config/routes.php file against the URI to
     * determine if the class/method need to be remapped.
     *
     * @access      private
     * @return      void
     */
    function _parse_routes()
    {
        // Do we even have any custom routing to deal with?
        // There is a default scaffolding trigger, so we'll look just for 1
        if (count($this->routes) == 1)
        {
            $this->_set_request($this->uri->segments);
            return;
        }

        // Turn the segment array into a URI string
        $uri = implode('/', $this->uri->segments);

        // Is there a literal match?  If so we're done
        if (isset($this->routes[$uri]))
        {
            $this->_set_request(explode('/', $this->routes[$uri]));
            return;
        }

        //Art
        $i = $this->_search_by_key($this->routes, self::MAIN, $uri);
        if ($i !== FALSE)
        {
            $this->_set_request(explode('/', $this->routes[$i][self::ROUTE]));
            return;
        }

        // Loop through the route array looking for wild-cards
        foreach ($this->routes as $key => $val)
        {
            if(is_int($key))
            {
                $key = $val[self::MAIN];
                $replace = (isset($val[self::REPLACE]) ? $val[self::REPLACE] : false);
                $reg_replace = (isset($val[self::REG_REPLACE]) ? $val[self::REG_REPLACE] : false);
                $redirect = (isset($val[self::REDIRECT]) ? $val[self::REDIRECT] : false);
                $val = $val[self::ROUTE];
            }
            // Convert wild-cards to RegEx
            $key = str_replace(':any', '.+', str_replace(':num', '[0-9]+', $key));

            // Does the RegEx match?
            if (preg_match('#^'.$key.'$#', $uri))
            {
                // Do we have a back-reference?
                if (strpos($val, '$') !== FALSE AND strpos($key, '(') !== FALSE)
                {
                    $val = preg_replace('#^'.$key.'$#', $val, $uri);
                }

                if(isset($replace) && $replace !== false){
                    $l = count($replace);

                    for($i = 0; $i < $l; ++$i){
                        $val = str_ireplace($replace[$i][self::REPLACE_KEY], $replace[$i][self::REPLACE_VALUE], $val);
                    }
                }
                if(isset($reg_replace) && $reg_replace !== FALSE){
                    $l = count($reg_replace);
                    for($i = 0; $i < $l; ++$i){
                        $val = preg_replace($reg_replace[$i][self::REPLACE_KEY], $reg_replace[$i][self::REPLACE_VALUE], $val);
                    }
                }
                if(isset($redirect) && $redirect == true){
                    $conf = load_class('Config');
                    $val = $conf->site_url($val);
                    header("Location: ".$val, TRUE, $redirect);
                    exit();
                }

                $this->_set_request(explode('/', $val));
                return;
            }
        }

        // If we got this far it means we didn't encounter a
        // matching route so we'll set the site default route
        $this->_set_request($this->uri->segments);
    }


    /**
     * Build the url using params in config by name of rule
     *
     * @param string Name of rule
     * @param array associative array of params
     * @return string url
     */
    function buildUrl($name, $array = null)
    {
        $i = 0;
        $i = $this->_search_by_key($this->routes, self::NAME, $name);
        
        if($i === FALSE)
        {
            log_message('ERROR', 'Try to create undefined url with name: '.$name);
            //log_message('ERROR', print_r($this->routes, true));
            return $this->config->site_url();
        }

        $rule = $this->routes[$i];

        //Наследование роутинга
        if(preg_match_all("#\[([\w_]+)\]#", $rule[self::URL], $mas)){
            $l = count($mas[1]);
            
            for($i = 0; $i < $l; $i++){
                $j = $this->_search_by_key($this->routes, self::NAME, $mas[1][$i]);
                if($j !== FALSE){
                    $parent_rule = $this->routes[$j];
                    $rule[self::URL] = str_replace('[' . $mas[1][$i] . ']', $parent_rule[self::URL], $rule[self::URL]);
                }
            }
        }
        
        foreach($array as $k => $v)
        {
            $rule[self::URL] = str_replace(':'.$k . '/', $v . '/', $rule[self::URL]);
        }

        if(preg_match("@:\w+$@", $rule[self::URL])){
            foreach($array as $k => $v){
                $rule[self::URL] = str_replace(':'.$k, $v, $rule[self::URL]);
            }
        }
        
        return $this->config->site_url(reduce_double_slashes($rule[self::URL]));
    }

}