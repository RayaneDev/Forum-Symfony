<?php

namespace App\Entity;


class TopicSearch
{

    private $research; 

    private $section; 


    public function getResearch(): ?string 
    {
        return $this->research; 
    }

    public function setResearch(string $research): self 
    {
        $this->research = $research; 

        return $this; 
    }

    public function getSection(): ?string 
    {
        return $this->section; 
    }

    public function setSection(string $section): self 
    {
        $this->section = $section; 

        return $this; 
    }
}
