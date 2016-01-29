<?php

/**
 * @Author: Scott Martin <sjm.dev1[at]gmail[dot]com>
 * @Filename: torrent_decoder.class.php
 * @Date: October 5th, 2010
 *
 * -- Description:
 * This is a torrent decoder class used to extract .torrent files into an
 * associative array of useable info.
 *
 * -- Usage:
 * require_once 'torrent_decoder.class.php';
 * $decoder = new torrent_decoder('path/to/file.torrent');
 * $torrent = $decoder->decode();
 * //print_r($torrent); //show all of the info provided by the torrent file
 *
 * -- Access Info:
 * $torrent now contains an array of useful info, for example
 * echo $torrent['announce']; //prints the announce URL
 *
 * @Liscense: GNU GPL V3
 -   Copyright (C) <2010>  <Scott Martin>
 -
 - This program is free software: you can redistribute it and/or modify
 - it under the terms of the GNU General Public License as published by
 - the Free Software Foundation, either version 3 of the License, or
 - (at your option) any later version.
 -
 - This program is distributed in the hope that it will be useful,
 - but WITHOUT ANY WARRANTY; without even the implied warranty of
 - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 - GNU General Public License for more details.
 -
 - You should have received a copy of the GNU General Public License
 - along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
class torrent_decoder
{
    private $contents = '';
    private $pos = 0;
    
    /**
     * When initiated the raw contents of the .torrent file are held 
     * in class member $contents.
     *
     * @access public
     * @param $data - data of torrent
     * @return void
     */
    function __construct($data)
    {
        $this->contents = $data;
    }
    
    /**
     * Starts the decoding method(s).
     * Throws exception if contents cannot be opened, is empty, or file cannot
     * be found.
     *
     * @access public
     * @param void
     * @return array
     */
    function decode()
    {
        if (empty($this->contents))
        {
            throw new exception('Torrent file is empty, cannot be opened, or cannot be found.');
            return;
        }
        
        $ret = $this->doChar();
        return $ret;
    }
    
    /**
     * Processes character at internal pointer position to check for an identifier.
     * Possible identifiers are 'd', 'l', 'i', and 0-9
     * Throws exception if an unknown character identifier is found.
     *
     * @access private
     * @param void
     * @return mixed
     */
    private function doChar()
    {    
        while ($this->contents[$this->pos] != 'e')
        {
            if ($this->contents[$this->pos] == 'd')
            {
                return $this->doDict();
            }
            elseif ($this->contents[$this->pos] == 'l')
            {
                return $this->doList();
            }
            elseif ($this->contents[$this->pos] == 'i')
            {
                return $this->doInt();
            }
            else
            {
                if (is_numeric($this->contents[$this->pos]))
                {
                    return $this->doString();
                } else
                {
                    throw new exception('Unknown character \'' . $this->contents[$this->pos] . '\' at position ' . $this->pos);
                    return;
                }
            }
        }
    }
    
    /**
     * Processes dictionary 'd' identifier.
     *
     * @access private
     * @param void
     * @return array
     */
    private function doDict()
    {
        $ret = array();
        $this->pos++;

        while ($this->contents[$this->pos] != 'e')
        {
            $key = $this->doString();

            if ($this->contents[$this->pos] == 'd')
            {
                $ret[$key] = $this->doDict();
            }
            elseif ($this->contents[$this->pos] == 'l')
            {
                $ret[$key] = $this->doList();
            }
            elseif ($this->contents[$this->pos] == 'i')
            {
                $ret[$key] = $this->doInt();
            } else
            {
                if (is_numeric($this->contents[$this->pos]))
                {
                    $ret[$key] = $this->doString();
                } else
                {
                    throw new exception('Unknown character \'' . $this->contents[$this->pos] . '\' at position ' . $this->pos);
                    return;
                }
            }
        }
        
        $this->pos++;
        
        return $ret;
    }
    
    /**
     * Processes strings found.
     *
     * @access private
     * @param void
     * @return string
     */
    private function doString()
    {
        $strlen = '';
        
        while (is_numeric($this->contents[$this->pos]))
        {
            $strlen .= $this->contents[$this->pos];
            $this->pos++;
        }
        
        if ($this->contents[$this->pos] == ':')
        {
            $this->pos++;
        }
        
        $strlen = intval($strlen);
        $str = substr($this->contents, $this->pos, $strlen);
        $this->pos = $this->pos + $strlen;
        
        return $str;
    }
    
    /**
     * Processes list 'l' identifiers and returns an array of 
     * items found in the list.
     *
     * @access private
     * @param void
     * @return array
     */
    private function doList()
    {
        $ret = array();
        $this->pos++;
        
        while ($this->contents[$this->pos] != 'e')
        {
            $ret[] = $this->doChar();
        }
        
        $this->pos++;

        return $ret;
    }
    
    /**
     * Processes integer 'i' identifier.
     *
     * @access private
     * @param void
     * @return integer
     */
    private function doInt()
    {
        $this->pos++;
        $int = '';
        
        while ($this->contents[$this->pos] != 'e')
        {
            $int .= $this->contents[$this->pos];
            $this->pos++;
        }
        
        $int = $int;
        $this->pos++;
        
        return $int;
    }
}