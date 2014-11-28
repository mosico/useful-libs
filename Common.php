<?php

class Common
{
    public static function getFileLines($fileName)
    {
        $lines = file($fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (empty($lines)) $lines = array();
        return $lines;
    }
    
    public static function getFileName($file)
    {
        $rpos = strrpos($file, '/');
        $name = substr($file, $rpos + 1);
        return  $name;
    }
    
    public static function getMonthList($from, $to)
    {
        $list = array();
        list($fromYear, $fromMonth, $fromDay) = explode('-', $from);
        list($toYear, $toMonth, $toDay) = explode('-', $to);
        
        $list[] = $from;
        $cy = (int) $fromYear;
        $cm = (int) $fromMonth;
        
        do {
            $cm++;
            $y = ($cm -1) / 12;
            $m = $cm % 12;
            $year = $cy + floor($y);
            $month = $m < 10 ? ($m == 0 ? 12 : "0{$m}") : $m;
            $next = "$year-$month-01";
            if ($next < $to) {
                $list[] = $next;
                $goNextLoop = true;
            } else {
                $goNextLoop = false;
            }
        } while ($goNextLoop);
        
        $list[] = $to;
        return $list;
    }
    
    public static function getWeekList($from, $to)
    {
        $list = array();
        $weekStart = 4;
        $weekEnd = 3;
        $weekSeconds = 86400 * 7;
        $fromTime = strtotime($from);
        $toTime = strtotime($to);
        
        $nextWeek = '';
        $fromWeek = date('N', $fromTime);
        $toWeek = date('N', $toTime);
        $list[] = $from;
        if ($fromWeek == $weekStart) {
            $nextWeek = $fromTime + $weekSeconds;
        } elseif ($fromWeek < $weekStart) {
            $list[] = date('Y-m-d', $fromTime + ($weekStart - $fromWeek) * 86400);
            $nextWeek = $fromTime + ($weekStart - $fromWeek + 7) * 86400;
        } else {
            $nextWeek = $fromTime + (7 - ($fromWeek - $weekStart)) * 86400;
        }
        
        while ($nextWeek < $toTime) {
            $list[] = date('Y-m-d', $nextWeek);
            $nextWeek += $weekSeconds;
        }
        $list[] = $to;
        
        return $list;
    }
    
    public static function realPrint($content)
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            $type = gettype($arg);
            switch ($type) {
            	case "string":
            	case "integer":
            	case "double":
            	    echo $arg, '<br>';
            	    break;
            	case "array":
            	    echo '<pre>';
            	    print_r($arg);
            	    echo '</pre>';
            	    break;
            	case "boolean":
            	case "object":
            	case "resource":
            	case "NULL":
            	case "unknown type":
            	    var_dump($arg);
            	    break;
            }
        }
        
        ob_flush();
        ob_end_flush();
        flush();
        ob_start();
    }
    
    public static function getDatetime()
    {
        return date('Y-m-d H:i:s');
    }
    
    public static function expendTime(&$curTime = 0, $prevTime = 0)
    {
        $curTime = time();
        $expendTime = 0;
        if ($prevTime) {
            $expendTime = $curTime - $prevTime;
        }
        return $expendTime;
    }
}


