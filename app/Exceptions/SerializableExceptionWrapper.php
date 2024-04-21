<?php

namespace App\Exceptions;

use Serializable;
use Exception;
use Throwable;

class SerializableExceptionWrapper extends Exception implements Serializable {
   
    protected string $file;
    protected int $line;
    protected string $traceAsString; 
    protected string $asString;
    protected string $class; 

    public function __construct(Throwable $throwable) 
    {   
        $this->file = $throwable->getFile();
        $this->line = $throwable->getLine();
        $this->traceAsString = $throwable->getTraceAsString();
        $this->asString = $throwable->__toString();
        $this->class = get_class($throwable);
        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());

        
    }

    public function getClass(): string
    {
        return $this->class;
    }


    public function __toString(): string
    {
        return $this->asString;
    }

    public function serialize()
    {
        return serialize([
            $this->message,
            $this->code,
            $this->file,
            $this->line,
            $this->traceAsString,
            $this->asString,
            $this->class
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->message,
            $this->code,
            $this->file,
            $this->line,
            $this->traceAsString,
            $this->asString,
            $this->class
        ) = unserialize($serialized);
    }
}

    

