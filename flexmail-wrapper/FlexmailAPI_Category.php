<?php

class FlexmailAPI_Category extends FlexmailAPI
{    
    /**
     * Create a new Category
     *
     * Parmeters example:
     * ------------------ 
     * $parameters = array (
     *      "categoryName" => "My Category" // string mandatory
     * );
     * 
     * @param  array $parameters Associative array with The name of the new category
     * 
     * @return categoryId
     */
    public function create ($parameters)
    {
        $request = FlexmailAPI::parseArray($parameters);
        
        $response = $this->execute("CreateCategory", $request);
        return FlexmailAPI::stripHeader($response);
    }
     
    /**
     * Update a Category
     * 
     * Parmeters example:
     * ------------------ 
     * $parameters = array (
     *      "categoryId"   => 10025         // int mandatory
     *      "categoryName" => "My Category" // string mandatory
     * );
     * 
     * @param  array $parameters Associative array with categoryId and categoryName
     *
     * @return void 
     */
    public function update ($parameters)
    {   
        $request = FlexmailAPI::parseArray($parameters);
        
        $response = $this->execute("UpdateCategory", $request);
        return FlexmailAPI::stripHeader($response);
    }
    
    /**
     * Delete a Category
     * 
     * Parmeters example:
     * ------------------ 
     * $parameters = array (
     *      "categoryId"   => 10025 // int mandatory
     * );
     * 
     * @param  array $parameters Associative array with categoryId
     *
     * @return void 
     */
    public function delete ($parameters)
    {
        $request = FlexmailAPI::parseArray($parameters);
        
        $response = $this->execute("DeleteCategory", $request);
        return FlexmailAPI::stripHeader($response);
        
    }
    
    /**
     * Get all Categories
     * 
     * @return void
     */
    public function getAll()
    {
        $request = null;
        
        $response = $this->execute("GetCategories", $request);
        return FlexmailAPI::stripHeader($response);
    }
}