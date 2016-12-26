<?php

namespace App\Services\Json;

use App\Services\Json\ReaderException as Exceptn;

class Reader 
{
    
    protected $options = [
        'file_path'   => null,
        'offset'      => 0,
        'items_count' => null
        ];
    public $offset = 0;
    public $endOfFile = false;
    
    
    public function __construct( $options = [] )
    {
        $this->setOptions($options);
        $this->offset = $this->options['offset'];
    }
    
    public function getItems()
    {
        $file =  $this->checkFile();
        $count = $this->checkCount();
        $offset = $this->options['offset'];
        $handle = fopen($file, 'r');
        fseek($handle, $offset);
        $result = [];
        $counter = 0;
        $buffer = '';
        $inObject = false;
        $charCounter = 0;
        while (($symbol = fgetc($handle)) !== false)
        {
            $charCounter++;
            if( !$inObject && $symbol != '{')
            {
                continue;
            }
            if($symbol == '{')
            {
                $inObject = true;
            }
            if($inObject)
            {
                $buffer .= $symbol;
            }
            if($symbol == '}')
            {
                $itemArray = json_decode($buffer, true);
                if(empty($itemArray)) continue;
                $inObject = false;
                $result[] = $itemArray;
                $counter++;
                $buffer = '';
            }
            if( $counter == $count )
            {
                break;
            }
        }
        $this->offset = ftell($handle);
        $this->endOfFile = feof($handle);
        fclose($handle);
        return $result;
    }
    
    protected function checkFile()
    {
        if( !isset($this->options['file_path']) )
        {
            throw new Exceptn(Exceptn::TEXT_FNS);
        }
        return $this->options['file_path'];
    }
    
    protected function checkCount()
    {
        if( !isset($this->options['items_count']) )
        {
            throw new Exceptn(Exceptn::TEXT_CNS);
        }
        return $this->options['items_count'];
    }
    
    protected function setOptions( $options = [] )
    {
        if( is_array($options) )
        {
            foreach( array_keys($this->options) as $opt_name )
            {
                if( isset($options[$opt_name]) )
                {
                    $set_name = 'set_' . $opt_name;
                    $this->$set_name($options[$opt_name]);
                }
            }
            return $this;
        }
        else 
        {
            throw new Exceptn(Exceptn::TEXT_OPT);
        }
    }
    
    protected function set_items_count($count)
    {
        if( is_integer($count) )
        {
            $this->options['items_count'] = $count;
            return $this;
        }
        throw new Exceptn(Exceptn::TEXT_CNT);
    }
    
    protected function set_offset($offset) 
    {
        if( is_integer($offset) )
        {
            $this->options['offset'] = $offset;
            return $this;
        }
        throw new Exceptn(Exceptn::TEXT_OFF);
    }
    
    protected function set_file_path($filepath)
    {
        if( !empty($filepath) && file_exists($filepath) && is_file($filepath))
        {
            $this->options['file_path'] = $filepath;
            return $this;
        }
        elseif(empty($filepath))
        {
            throw new Exceptn(Exceptn::TEXT_FEM);
        }
        elseif(!file_exists($filepath))
        {
            throw new Exceptn(Exceptn::TEXT_FNE);
        }
        elseif(!is_file($filepath))
        {
            throw new Exceptn(Exceptn::TEXT_NFL);
        }
    }
    
}