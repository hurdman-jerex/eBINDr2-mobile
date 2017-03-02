<?php

class category_API extends hapi {
    /*
     * List sub-categories or content in the category
     */

    public function listing() {

        return $this->read("select tc.cid, tc.parent, tc.name, tc.description, (select title from trainingcontent where tid=tc.parent) as parentTitle from trainingcategories as tc", 0);
//        return $this->read("select * from trainingcategories order by ifnull(parent,cid), cid");
//
//        // if the category is not set then we'll pull top level
//        if (!isset($this->segments[3]) || $this->segments[3] == '0' || $this->segments[3] == 'null') {
//            return $this->read("select * from trainingcategories where parent is null");
//        }
//
//        // let's see now that we have a category if we have any sub categories
//        $this->bind('category', $this->segments[4]);
//        $cats = $this->read("select * from trainingcategories where account = '|account|' and parent = '|category|'");
//        if (sizeof($cats) > 0)
//            return $cats;
//        else {
//            return $this->read("select * from trainingcontentcategory cat inner join trainingcontent con on cat.content = con.tid and cat.account = con.account where cat.account = '|account|' and cat.category = '|category|'");
//        }
    }
    
    public function getTrainingCategoryByID() {
        $this->bind('cid', $this->segments[3]);

        return $this->read("select * from trainingcategories where  cid = '|cid|'", 0);
    }
    /*
     * Add a category
     */

    public function add() {

        $this->bind('name', $_POST['name']);
        $this->bind('description', $_POST['description']);

        if (isset($_POST['parent']) && $_POST['parent'] != "") {
            $this->bind('parent', $_POST['parent']);
            $sql = "insert into trainingcategories ( parent, name, description) values ( '|parent|', '|name|', '|description|')";
        } else {
            $sql = "insert into trainingcategories ( name, description) values ( '|name|', '|description|')";
        }

        $this->write("trainingcontent:add", $sql);
    }

    /*
     * Edit a category
     */

    public function edit() {
        $this->bind('cid', $this->segments[3]);
        $this->bind('name', $_POST['name']);
        $this->bind('description', $_POST['description']);
        
        if (isset($_POST['parent']) && $_POST['parent'] != "") {
            $this->bind('parent', $_POST['parent']);
            $sql = "update trainingcategories set parent= '|parent|', name= '|name|',description= '|description|' where cid='|cid|' ";
        } else {
            $sql = "update trainingcategories set name= '|name|',description= '|description|' where cid='|cid|' ";
        }

        $this->write("trainingcontent:edit",$sql);
    }

    /*
     * Remove a category
     */

    public function remove() {
        $this->bind('cid', $this->segments[3]);

        $this->write("trainingcontent:delete","delete from trainingcategories where cid = '|cid|'");
    }

}

?>