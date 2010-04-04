<?php
/**
 * Description of MY_URI
 *
 * @author toxa
 */
class MY_URI extends CI_URI{

    /**
     * Generate a key value pair from the URI string or Re-routed URI string
     *
     * @access	private
     * @param	integer	the starting segment number
     * @param	array	an array of default values
     * @param	string	which array we should use
     * @return	array
     */
    function _uri_to_assoc($n = 3, $default = array(), $which = 'segment')
    {
        if ( ! is_numeric($n))
        {
            return $default;
        }

        if ($which == 'segment')
        {
            $total_segments = 'total_segments';
            $segment_array = 'segment_array';
        }
        else
        {
            $total_segments = 'total_rsegments';
            $segment_array = 'rsegment_array';
        }

        if (isset($this->keyval[$n]))
        {
            return $this->keyval[$n];
        }

        if ($this->$total_segments() < $n)
        {
            if (count($default) == 0)
            {
                return array();
            }

            $retval = array();
            $keys = array_keys($default);
            if(!is_numeric($keys[0])){
                foreach ($default as $key => $val)
                {
                    $retval[$key] = $val;
                }
            } else {
                foreach ($default as $val)
                {
                    $retval[$val] = FALSE;
                }
            }
            return $retval;
        }

        $segments = array_slice($this->$segment_array(), ($n - 1));

        $i = 0;
        $lastval = '';
        $retval  = array();
        foreach ($segments as $seg)
        {
            if ($i % 2)
            {
                $retval[$lastval] = $seg;
            }
            else
            {
                $retval[$seg] = FALSE;
                $lastval = $seg;
            }

            $i++;
        }

        if (count($default) > 0)
        {
            $keys = array_keys($default);
            if(is_numeric($keys[0])){
                foreach ($default as $val)
                {
                    if ( ! array_key_exists($val, $retval))
                    {
                        $retval[$val] = FALSE;
                    }
                }
            } else {
                foreach ($default as $key => $val)
                {
                    if ( ! array_key_exists($key, $retval))
                    {
                        $retval[$key] = $val;
                    }
                }
            }
        }

        // Cache the array for reuse
        $this->keyval[$n] = $retval;
        return $retval;
    }

    /**
     * Generate a URI string from an associative array
     *
     *
     * @access	public
     * @param	array	an associative array of key/values
     * @return	array
     */
    function assoc_to_uri($array)
    {
        $temp = array();
        foreach ((array)$array as $key => $val)
        {
            if($val !== FALSE){
                $temp[] = $key;
                $temp[] = $val;
            }
        }

        return implode('/', $temp);
    }

    /**
     * Parse the REQUEST_URI
     *
     * Due to the way REQUEST_URI works it usually contains path info
     * that makes it unusable as URI data.  We'll trim off the unnecessary
     * data, hopefully arriving at a valid URI that we can use.
     *
     * @access	private
     * @return	string
     */
    function _parse_request_uri()
    {
        if ( ! isset($_SERVER['REQUEST_URI']) OR $_SERVER['REQUEST_URI'] == '')
        {
            return '';
        }

        if( !isset($_SERVER['PATH_INFO']) )
        {
            $p_info = trim($_SERVER['REQUEST_URI']);
            if( strrpos( $p_info, "?") ){ $p_info = substr( $p_info, 0, strrpos( $p_info, "?")); }
            $request_uri = $p_info;
        } else {
            $request_uri = preg_replace("|/(.*)|", "\\1", str_replace("\\", "/", $_SERVER['REQUEST_URI']));
        }

        if ($request_uri == '' OR $request_uri == SELF)
        {
            return '';
        }

        $fc_path = FCPATH.SELF;
        if (strpos($request_uri, '?') !== FALSE)
        {
            $fc_path .= '?';
        }

        $parsed_uri = explode("/", $request_uri);

        $i = 0;
        foreach(explode("/", $fc_path) as $segment)
        {
            if (isset($parsed_uri[$i]) && $segment == $parsed_uri[$i])
            {
                $i++;
            }
        }

        $parsed_uri = implode("/", array_slice($parsed_uri, $i));

        if ($parsed_uri != '')
        {
            $parsed_uri = '/'.$parsed_uri;
        }

        return $parsed_uri;
    }
}
?>
