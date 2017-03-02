<?php

class content_API extends hapi {

    public function listing() {

        return $this->read("select tc.title,tc.tid, tc.description,tc.created, tc.content, tc.author, (select firstname from person where pid=tc.author) as firstname, (select lastname from person where pid=tc.author) as lastname  from trainingcontent as tc where tc.deleted IS NULL",0);
        //return $this->read("select todos.*, if(due<curdate(),1,0) as overdue from todos where completed is null and account = '|account|' and assigned = '|assigned|' order by due ASC", 0, true);
    }

    public function getTrainingContentByID() {
        $this->bind('tid', $this->segments[3]);

        return $this->read("select * from trainingcontent where  tid = '|tid|'", 0);
    }

    /*
     * Categorize content
     */

    public function categorize() {
        $this->bind('account', $this->segments[3]);
        $this->bind('tid', $this->segments[4]);
        $this->bind('category', $this->segments[5]);

        echo $this->write("replace into trainingcontentcategory (account, content, category, added) values ('|account|', '|tid|', '|category|', now())");
    }

    /*
     * Add content
     */

    public function add() {

        $this->bind('person', $this->segments[3]);
        $this->bind('title', $_POST['title']);
        $this->bind('description', $_POST['description']);
        $this->bind('content', $_POST['content']);

        return $this->write("trainingcontent:add","insert into trainingcontent ( author, title, description, created, content) values (  '|person|', '|title|', '|description|', now(), '|content|' )");
    }

    /*
     * Edit Content
     */

    public function edit() {
        $this->bind('tid', $this->segments[3]);
        $this->bind('title', $_POST['title']);
        $this->bind('description', $_POST['description']);
        $this->bind('content', $_POST['content']);

        $this->write("trainingcontent:edit","update trainingcontent set lastedit = now(), title = '|title|', description = '|description|', content = '|content|' where tid = '|tid|'");
    }

    /*
     * Delete content
     */

    public function remove() {
        $this->bind('tid', $this->segments[3]);
        $this->write("trainingcontent:remove","update trainingcontent set tid = -tid, deleted = now() where tid = '|tid|'");
    }

}

?>