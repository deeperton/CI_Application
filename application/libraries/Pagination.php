<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 4.3.2 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Pagination Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Pagination
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/pagination.html
 */
class Pagination {
    const URL_RENDER_ROUTE = 'route';
    const URL_RENDER_ASSOC = 'assoc';

    private $base_url_name      	= ''; // The page we are linking to
    private $url_params             = array();
    private $page_num_param_name    = ':page_num';
    private $page_count_param_name  = ':page_count';

    private $render_type            = self::URL_RENDER_ROUTE;

    private $per_page_urls          = false;

    private $uri_assoc_start_uri    = '';
    private $uri_assoc_name_page    = 'page';
    private $uri_assoc_name_count    = 'count';

    private $total_rows  		= ''; // Total number of items (database results)
    private $per_page	 		= 10; // Max number of items you want shown per page

    private $def_per_page	 		= 10; // Default number of items you want shown per page
    private $num_links			=  2; // Number of "digit" links to show before/after the currently viewed page
    private $cur_page	 		=  false; // The current page being viewed
    private $first_link   		= '&lsaquo; First';
    private $next_link			= '&gt;';
    private $prev_link			= '&lt;';
    private $last_link			= 'Last &rsaquo;';
    private $uri_segment		= 3;
    private $full_tag_open		= '';
    private $full_tag_close		= '';
    private $first_tag_open		= '';
    private $first_tag_close	= '&nbsp;';
    private $last_tag_open		= '&nbsp;';
    private $last_tag_close		= '';
    private $cur_tag_open		= '&nbsp;<strong>';
    private $cur_tag_close		= '</strong>';
    private $next_tag_open		= '&nbsp;';
    private $next_tag_close		= '&nbsp;';
    private $prev_tag_open		= '&nbsp;';
    private $prev_tag_close		= '';
    private $num_tag_open		= '&nbsp;';
    private $num_tag_close		= '';

    /**
     * Constructor
     *
     * @access	public
     * @param	array	initialization parameters
     */
    public function CI_Pagination($params = array())
    {
        if (count($params) > 0)
        {
            $this->initialize($params);
        }

        log_message('debug', "PaginationEx Class Initialized");
    }

    // --------------------------------------------------------------------

    /**
     * Initialize Preferences
     *
     * @access	public
     * @param	array	initialization parameters
     * @return	void
     */
    public function initialize($params = array())
    {
        if (count($params) > 0)
        {
            foreach ($params as $key => $val)
            {
                if (isset($this->$key))
                {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * Ускорение формирования урла при наличии автоматического формирования
     *
     * @param int $n номер страницы или пустая строка
     * @return str готовый урл
     */
    private function makeUrl($n){
        static $url;
        if($this->render_type == self::URL_RENDER_ROUTE){
            if(!isset($url)){
                $url = site_url($this->base_url_name, $this->url_params);
                if($this->def_per_page == $this->per_page){
                    $url = str_replace($this->page_count_param_name, '', $url);
                } else {
                    $url = str_replace($this->page_count_param_name, $this->per_page, $url);
                }
            }

            return str_replace($this->page_num_param_name, $n, $url);

        } elseif( $this->render_type == self::URL_RENDER_ASSOC ){

            if(!isset($url)){
                $url = $this->uri_assoc_start_uri . '/' . $this->uri_assoc_name_page . '/{_page_}';
                if($this->per_page != $this->def_per_page){
                    $url .= '/' . $this->uri_assoc_name_count . '/' . $this->per_page;
                }
                $url = preg_replace("#([^:])//+#", "\\1/", $url.'/');
            }
            if($n == ''){
                return str_replace($this->uri_assoc_name_page . '/{_page_}', '', $url);
            }
            return str_replace('{_page_}', $n, $url);
        }
    }

    // --------------------------------------------------------------------

    /**
     * Generate the pagination links
     *
     * @access	public
     * @return	string
     */
    public function create_links()
    {
        // If our item count or per-page total is zero there is no need to continue.
        if ($this->total_rows == 0 OR $this->per_page == 0)
        {
            return '';
        }


        // Determine the current page number.
        $CI =& get_instance();
        if($this->cur_page === false){
            if ($CI->uri->segment($this->uri_segment) != 0)
            {
                $this->cur_page = $CI->uri->segment($this->uri_segment);

                // Prep the current page - no funny business!
                $this->cur_page = (int) $this->cur_page;
            }
        }

        $this->num_links = (int)$this->num_links;

        if ($this->num_links < 1)
        {
            show_error('Your number of links must be a positive number.');
        }

        if ( ! is_numeric($this->cur_page))
        {
            $this->cur_page = 0;
        }
        if($this->per_page_urls === false){
            $output = $this->defaultUrlsBuild();
        } else {
            $output = $this->per_pageUrlsBuild();
        }
        // Kill double slashes.  Note: Sometimes we can end up with a double slash
        // in the penultimate link so we'll kill all double slashes.
        $output = preg_replace("#([^:])//+#", "\\1/", $output);

        // Add the wrapper HTML if exists
        $output = $this->full_tag_open.$output.$this->full_tag_close;

        return $output;
    }

    protected function per_pageUrlsBuild(){
        $num_pages = $this->total_rows - 1;
        // Is the page number beyond the result range?
        // If so we show the last page
        if ($this->cur_page > $this->total_rows)
        {
            $this->cur_page = ($num_pages - 1) * $this->per_page;
        }

        $uri_page_number = $this->cur_page;
        //$this->cur_page = floor(($this->cur_page/$this->per_page) + 1);

        // Calculate the start and end numbers. These determine
        // which number to start and end the digit links with
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;;
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        // And here we go...
        $output = '';

        // Render the "First" link
        if  ($this->cur_page > ($this->num_links + 1))
        {
            $output .= $this->first_tag_open.'<a href="'.$this->makeUrl('').'">'.$this->first_link.'</a>'.$this->first_tag_close;
        }

        // Render the "previous" link
        if  ($this->cur_page != 1)
        {
            $i = $uri_page_number - 1;
            if ($i == 0) $i = '';
            $output .= $this->prev_tag_open.'<a href="'.$this->makeUrl($i).'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
        }

        // Write the digit links
        for ($loop = $start -1; $loop <= $end; $loop++)
        {
            $i = ($loop * $this->per_page) - $this->per_page;

            if ($i >= 0)
            {
                if ($this->cur_page == $loop)
                {
                    $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
                }
                else
                {
                    $n = ($i == 0) ? '' : $i;
                    $output .= $this->num_tag_open.'<a href="'.$this->makeUrl($loop).'">'.$loop.'</a>'.$this->num_tag_close;
                }
            }
        }

        // Render the "next" link
        if ($this->cur_page < $num_pages)
        {
            $output .= $this->next_tag_open.'<a href="'.$this->makeUrl($this->cur_page + 1).'">'.$this->next_link.'</a>'.$this->next_tag_close;
        }

        // Render the "Last" link
        if (($this->cur_page + $this->num_links) < $num_pages)
        {
            $output .= $this->last_tag_open.'<a href="'.$this->makeUrl($num_pages).'">'.$this->last_link.'</a>'.$this->last_tag_close;
        }
        return $output;
    }

    protected function defaultUrlsBuild(){
        // Calculate the total number of pages
        $num_pages = ceil($this->total_rows / $this->per_page);

        // Is there only one page? Hm... nothing more to do here then.
        if ($num_pages == 1)
        {
            return '';
        }
        // Is the page number beyond the result range?
        // If so we show the last page
        if ($this->cur_page > $this->total_rows)
        {
            $this->cur_page = ($num_pages - 1) * $this->per_page;
        }

        $uri_page_number = $this->cur_page;
        $this->cur_page = floor(($this->cur_page/$this->per_page) + 1);

        // Calculate the start and end numbers. These determine
        // which number to start and end the digit links with
        $start = (($this->cur_page - $this->num_links) > 0) ? $this->cur_page - ($this->num_links - 1) : 1;
        $end   = (($this->cur_page + $this->num_links) < $num_pages) ? $this->cur_page + $this->num_links : $num_pages;

        // And here we go...
        $output = '';

        // Render the "First" link
        if  ($this->cur_page > ($this->num_links + 1))
        {
            $output .= $this->first_tag_open.'<a href="'.$this->makeUrl('').'">'.$this->first_link.'</a>'.$this->first_tag_close;
        }

        // Render the "previous" link
        if  ($this->cur_page != 1)
        {
            $i = $uri_page_number - $this->per_page;
            if ($i == 0) $i = '';
            $output .= $this->prev_tag_open.'<a href="'.$this->makeUrl($i).'">'.$this->prev_link.'</a>'.$this->prev_tag_close;
        }

        // Write the digit links
        for ($loop = $start -1; $loop <= $end; $loop++)
        {
            $i = ($loop * $this->per_page) - $this->per_page;

            if ($i >= 0)
            {
                if ($this->cur_page == $loop)
                {
                    $output .= $this->cur_tag_open.$loop.$this->cur_tag_close; // Current page
                }
                else
                {
                    $n = ($i == 0) ? '' : $i;
                    $output .= $this->num_tag_open.'<a href="'.$this->makeUrl($n).'">'.$loop.'</a>'.$this->num_tag_close;
                }
            }
        }

        // Render the "next" link
        if ($this->cur_page < $num_pages)
        {
            $output .= $this->next_tag_open.'<a href="'.$this->makeUrl($this->cur_page * $this->per_page).'">'.$this->next_link.'</a>'.$this->next_tag_close;
        }

        // Render the "Last" link
        if (($this->cur_page + $this->num_links) < $num_pages)
        {
            $i = (($num_pages * $this->per_page) - $this->per_page);
            $output .= $this->last_tag_open.'<a href="'.$this->makeUrl($i).'">'.$this->last_link.'</a>'.$this->last_tag_close;
        }
        return $output;
    }
}
// END Pagination Class

/* End of file Pagination.php */
/* Location: ./engine/libraries/Pagination.php */