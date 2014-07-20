<?php
/*
Copyright (c) 2014 Michel Petit <petit.michel@gmail.com>

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Malenki;

/**
 * CalKalQal to have calendar structure. 
 * 
 * @author Michel Petit <petit.michel@gmail.com> 
 * @license MIT
 */
class CalKalQal 
{
    public $one_letter_day = false;
    public $show_outside_days = false;
    public $month;
    public $year;
    public $arr_links = array();
    
    // first day of the month into week
    private $firstday;
    // last day of the month into week
    private $lastday;


    /**
     * Constructor
     * 
     * @param mixed $month 
     * @param mixed $year
     * @access public
     * @return void
     */
    public function __construct($month = null, $year = null)
    {
        if(!is_numeric($month))
        {
            $this->month = (int) date('n');
        }
        else
        {
            $this->month = $month;
        }

        if(!is_numeric($year))
        {
            $this->year = (int) date('Y');
        }
        else
        {
            $this->year = $year;
        }
        
        // start month
        $time = mktime(0, 0 , 0, $this->month, 1, $this->year);
        $this->days = (int) date('t', $time);
        $this->firstday = (int) date('w', $time);
        
        if($this->firstday == 0)
        {
            $this->firstday = 7;
        }

        // last day
        $time = mktime(0, 0 , 0, $this->month, $this->days, $this->year);
        $this->lastday = (int) date('w', $time);
        if($this->lastday == 0)
        {
            $this->lastday = 7;
        }
    }

    /**
     * Add link for given date. 
     * 
     * A link is added to the date, without change number display.
     *
     * @param string $url 
     * @param integer $day 
     * @access public
     * @return void
     */
    public function addLink($day, $url)
    {
        $this->arr_links[$day] = $url;
        return $this;
    }

    public function addLinkTitle($url)
    {
        $this->url_title = $url;
        return $this;
    }

    private function addCountPrevNextLink($previous, $next)
    {

        if(!is_null($previous))
        {
            $this->prev_next_links++;
        }
        if(!is_null($next))
        {
            $this->prev_next_links++;
        }
        return $this;
    }

    public function addLinkMonth($previous, $next)
    {
        $this->arr_url_month = array('previous' => $previous, 'next' => $next);
        $this->addCountPrevNextLink($previous, $next);
        return $this;
    }

    public function addLinkYear($previous, $next)
    {
        $this->arr_url_year = array('previous' => $previous, 'next' => $next);
        $this->addCountPrevNextLink($previous, $next);
        return $this;
    }

    public function getMonthName()
    {
        $arr_month_names = array(
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December" 
        );
        return $arr_month_names[$this->month - 1];
    }

    private function getOutsideDaysBefore()
    {
        $int_pad = $this->firstday - 1;
        
        if($int_pad <= 0)
        {
            return array();
        }

        if($this->show_outside_days)
        {
            $previous_month = ($this->month == 1) ? 12 : $this->month - 1;
            $previous_year = ($this->month == 1) ? $this->year - 1 : $this->year;

            $time_previous = mktime(0, 0 , 0, $previous_month, 1, $previous_year);
            $days_in_month_previous = (int) date('t', $time_previous);
            $day_previous = $days_in_month_previous - ($this->firstday - 2);
            
            $arr_out = array_keys(array_fill($day_previous, $int_pad, '&nbsp;'));
        }
        else
        {
            $arr_out = array_fill(0, $int_pad, '&nbsp;');
        }

        return $arr_out;
    }
    
    private function getOutsideDaysAfter()
    {
        $int_pad = 7 - $this->lastday;

        if($int_pad <= 0)
        {
            return array();
        }

        if($this->show_outside_days)
        {
            $arr_out = array_keys(array_fill(1, $int_pad, '&nbsp;'));
        }
        else
        {
            $arr_out = array_fill(0, $int_pad, '&nbsp;');
        }

        return $arr_out;
    }

    /**
     * render 
     * 
     * @todo Must not be like that. Must allow XML or JSON only
     */
    public function render()
    {
        $dom_table = new DOMDocument();
        $tag_table = $dom_table->createElement('table');
        $tag_table->setAttribute('class','calendar');

        $txt_title = $this->getMonthName().' '.$this->year;
            
        if(isset($this->url_title))
        {
            $tag_a_title = $dom_table->createElement('a', $txt_title);
            $tag_a_title->setAttribute('href', $this->url_title);
            $tag_th_title = $dom_table->createElement('th');
            $tag_th_title->appendChild($tag_a_title);
        }
        else
        {
            $tag_th_title = $dom_table->createElement('th', $txt_title);
        }


        // TODO here, variable number of cells
        $tag_th_title->setAttribute('colspan', 7);
        $tag_th_title->setAttribute('class', 'calendar_title');

        $tag_thead           = $dom_table->createElement('thead');
        $tag_thead_row_title = $dom_table->createElement('tr');
        $tag_thead_row_days  = $dom_table->createElement('tr');

        $tag_thead_row_title->appendChild($tag_th_title);

        $arr_day_names = array(
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday"
        );
        foreach($arr_day_names as $d)
        {
            if($this->one_letter_day)
            {
                $tag_th_day = $dom_table->createElement('th', mb_substr($d, 0, 1, 'UTF-8'));
                $tag_th_day->setAttribute('title', $d);
            }
            else
            {
                $tag_th_day = $dom_table->createElement('th', $d);
            }

            $tag_thead_row_days->appendChild($tag_th_day);
        }

        $tag_thead->appendChild($tag_thead_row_title);
        $tag_thead->appendChild($tag_thead_row_days);
        $tag_table->appendChild($tag_thead);

        $tag_tbody = $dom_table->createElement('tbody');

        $daycode = 1; 

        $cnt_boxes = 0;
        $tr = $dom_table->createElement('tr');

        foreach($this->getOutsideDaysBefore() as $d)
        {
            $tr->appendChild($dom_table->createElement('td', $d));
            $cnt_boxes++;
            $daycode++;
        }

        // loop for each month’s day
        for ($numday = 1; $numday <= $this->days; $numday++, $daycode++) 
        {
            if(isset($this->arr_links[$numday]))
            {
                $tag_td_day = $dom_table->createElement('td');
                $tag_a_day = $dom_table->createElement('a', $numday);
                $tag_a_day->setAttribute('href', $this->arr_links[$numday]);
                $tag_td_day->appendChild($tag_a_day);
            }
            else
            {
                $tag_td_day = $dom_table->createElement('td', $numday);
            }

            $arr_day_class = array('calendar_monthday');

            if($numday == (int) date('j'))
            {
                $arr_day_class[] = 'calendar_today';
            }
            
            $tag_td_day->setAttribute('class', implode(' ', $arr_day_class));
            $tr->appendChild($tag_td_day);
            $cnt_boxes++;

            if($cnt_boxes == 7)
            {
                $tag_tbody->appendChild($tr);
                $tr = $dom_table->createElement('tr');
                $cnt_boxes = 0;
            }

        }

        // Put blank box to end week if needed
        if($cnt_boxes != 7)
        {
            foreach ($this->getOutsideDaysAfter() as $d)
            {
                $tr->appendChild($dom_table->createElement('td', $d));
                
                $cnt_boxes++; $i++; $daycode++; 
                
                if($cnt_boxes == 7)
                {
                    $tag_tbody->appendChild($tr);
                }
            }
        }

        $tag_table->appendChild($tag_tbody);

        return $dom_table->saveXML($tag_table);
    }
}

