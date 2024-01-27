<?php 

class Filter
{
    protected $filters; 
    protected $logs; 

    public function has($key, $function_to_check = false) {
        if ($function_to_check === false) {
            if (!empty($this->filters[$key])) {
                return true;
            }
            return false; 
        }
        
        if (!empty($this->filters[$key])) {
            foreach($this->filters[$key] AS $priority => $func) {
                if ($func['function'] == $function_to_check) {
                    return $priority; 
                }
            }
        }
        
        return false;
    }
    
    public function add($key, $callback, $priority, $accepted = 1) 
    {
        $this->filters[$key][] = array(
            'function' => $callback, 
            'priority' => $priority, 
            'accepted' => $accepted, 
        );
        return true; 
    }
    
    public function remove($key, $callback, $priority, $accepted = 1) 
    {
        if (isset($this->filters[$key][$priority])) {
            unset($this->filters[$key][$priority]);
            return true;
        }
        
        return false; 
    }
    
    public function runFilter($key, $value) 
    {
	    if (isset($this->filters[$key])) {
		    usort($this->filters[$key], array($this, 'sort'));

            $str = $value; 
  			foreach($this->filters[$key] AS $p => $callback) {
                if ($callback['accepted'] == 1) {
                    $value = call_user_func($callback['function'], $value);
                } else {
                    $value = call_user_func_array($callback['function'], $value);
                }
  			}
            
            return $value; 
		}

        else {
            return $value;
        }
  	}
    
    function sort($a, $b) 
    {
        if ($a['priority'] == $b['priority']) {
            return 0;
        }
        return ($a['priority'] < $b['priority']) ? -1 : 1;
    }

    public function runEvent($key, $value) 
    {
	    if (isset($this->filters[$key])) {
            usort($this->filters[$key], array($this, 'sort'));
        
  			foreach($this->filters[$key] AS $p => $callback) {
                if (is_callable($callback['function'])) {
                    if (is_array($value)) {
                        call_user_func_array($callback['function'], $value);
                    } else {
                        call_user_func($callback['function'], $value);
                    }
                }
  			}
		}
        
        else {
            return $value;
        }
  	}
}