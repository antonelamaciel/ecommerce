<?php
namespace App\Model;

class Search
{
    private $string = '';
    private $categories = [];
    private $subcategories = [];

    public function getString() { return $this->string; }
    public function setString($string) { $this->string = $string; return $this; }

    public function getCategories() { return $this->categories; }
    public function setCategories($categories) { $this->categories = $categories; return $this; }

    public function getSubcategories() { return $this->subcategories; }
    public function setSubcategories($subcategories) { $this->subcategories = $subcategories; return $this; }
}