<?php
ini_set('display_errors','0');

class quiz_API extends hapi {

	/*
		Returns the basic quiz information
	*/
	public function info(){
		$this->bind( 'id', $this->segments[0] );
		return $this->read( "select * from common.quiz where id = '|id|'", 0 );	
	}
	
	/*
		Returns the quiz questions and question choices
	 */	 
	public function questions(){
		$this->bind( 'id', $this->segments[0] );
		$questions = $this->read("select a.question_id, b.qtext, b.answer, b.random_choice_order from common.quiz_question a RIGHT JOIN common.question b ON a.question_id = b.id where a.quiz_id = '|id|' order by a.question_id", 0);

		$qdata = array();
		foreach($questions as $q){
			$choices = $this->read( "select * from common.question_choices where question_id = " . $q["question_id"] . " order by id", 0);			
			
			/*
			if( $q['random_choice_order'] == 1 && ! empty($choices) ){
				shuffle($choices);
			}
			*/
				
			$qdata[] = array("question_id" => $q["question_id"],
							 "question_text" => $q["qtext"],
							 "answer" => $q["answer"],
							 "random_choice_order" => $q["random_choice_order"],
							 "choices" => $choices);			
		}
		
		return $qdata;
	}
	
	/*
		Save the quiz answer, determines the result and returns the score and total number of questions
	 */
	public function savequiz(){		
		
		$this->bind( 'id', $this->segments[0] );
		$this->bind( 'person', $_POST['person'] );
		
		$this->write("person_quiz:save", "insert into person_quiz(quiz_id, person, date_taken) values(|id|, '|person|', curdate())");
		
		$person_quiz_id = mysql_insert_id();
		$score = 0;		
		$answers = explode(',', $_POST['answers']);
		$numberAnswer = 1;
		$wrongArray = array();

		foreach($answers as $answer){
			$a = explode('-', $answer);
			$qid = $a[0];
			$val = $a[1];
			
			// Check if answer is correct or not
			$result = $this->read("select id, answer from common.question where id = " . $qid, 0);
			
			$remark = ($result[0]['answer'] == $val)? 1 : 0;
			$score = ($remark == 1)? $score+1 : $score;
			if ($remark != 1) $wrongArray[$numberAnswer] = $result[0]['id'];
			$numberAnswer++;
			
			$this->write("person_quiz_answer:save", "insert into person_quiz_answer(quiz_id, question_id, answer, remark) values('{$person_quiz_id}', {$qid} , {$val} , {$remark} )");
		}
		
		$result = $this->read("select count(*) as questioncount from common.quiz_question where quiz_id = '|id|'", 0);
		$qcount = $result[0]['questioncount'];
		
		$this->write("person_quiz:update", "update person_quiz set score = {$score} , num_question = {$qcount} where id='{$person_quiz_id}'");		
		
		return array('score' => $score, 'total' => $qcount, 'wrongs' => $wrongArray);		
	}
	
	/*
		Returns the score and total number of questions of a person quiz
	 */
	public function result(){
		$this->bind( 'id', $this->segments[0] );  // person quiz id
		$score = $this->read("select score, quiz_id from person_quiz where id = '|id|'", 0);
		$result = $this->read("select count(*) as questioncount from common.quiz_question where quiz_id = '". $score[0]['quiz_id'] ."'", 0);				
		
		return array('score' => $score[0]['score'], 'total' => $result[0]['questioncount']);				
	}
	
	public function getquestion(){
		$this->bind( 'id', $_POST['qid'] );
		return $this->read("select * from common.question where id = '|id|'");
	}

	public function getquestionbyqid(){
		$this->bind( 'id', $this->segments[0] );
		return $this->read("select * from common.question where id = '|id|'");
	}	
	
	public function addquiz(){
		$this->bind( 'title', $_POST['title'] );
		$this->bind( 'created_by', $_POST['person'] );
		$this->bind( 'status', $_POST['status'] );
		$this->bind( 'random_question_order', $_POST['random'] );
		
		$this->write("common::quiz:add", "insert into common.quiz(title, created_by, status, random_question_order, date_created) values ('|title|', '|created_by|', '|status|', '|random_question_order|', curdate())");
		return array( 'qid' => mysql_insert_id() );	
	}

	public function addquestion(){
		$this->bind( 'qtext', $_POST['qtext'] );
		$this->bind( 'answer', $_POST['answer'] );
		$this->bind( 'random_choice_order', $_POST['random'] );
		
		$this->write("common::uiz:add", "insert into common.question(qtext, answer, random_choice_order) values ('|qtext|', '|answer|', '|random_choice_order|')");		
		
		return array( 'qid' => mysql_insert_id() );	
	}
	
	public function addchoice(){
		$this->bind( 'ctext',  $_POST['ctext'] );
		$this->bind( 'question_id',  $_POST['question_id'] );
		$this->bind( 'sort_order',  $_POST['sort_order'] );		
		
		$this->write("common::quiz_question:add", "insert into common.question_choices(ctext, question_id, sort_order) values ('|ctext|', '|question_id|', '|sort_order|')");
		
		return array( 'cid' => mysql_insert_id() );	
	}
	
	public function addquizquestion(){
		$this->bind( 'quiz_id',  $_POST['quiz_id'] );
		$this->bind( 'question_id',  $_POST['question_id'] );
		$this->bind( 'sort_order',  $_POST['sort_order'] );		
		
		return $this->write("common::quiz_question:add", "insert into common.quiz_question(quiz_id, question_id, sort_order) values ('|quiz_id|', '|question_id|', '|sort_order|')");	
	}
		
	public function listing(){
		$this->bind('created_by', $this->segments[0]);
		return $this->read("select a.*, count(*) as questions from common.quiz as a LEFT JOIN common.quiz_question b  ON a.id = b.quiz_id group by 1", 0);
	}
	
	public function editquiz(){
		$this->bind( 'id', $_POST['quiz_id'] );
		$this->bind( 'title', $_POST['title'] );
		$this->bind( 'status', $_POST['status'] );
		$this->bind( 'random_question_order', $_POST['random'] );
		
		return $this->write("common::quiz:edit", "update common.quiz set title='|title|', created_by='|created_by|', status='|status|', 
								random_question_order='|random_question_order|', date_created=curdate() 
								where id = '|id|'");
	}

	public function editquestion(){
		$this->bind( 'id', $_POST['id'] );
		$this->bind( 'qtext', $_POST['qtext'] );
		$this->bind( 'answer', $_POST['answer'] );
		$this->bind( 'random_choice_order', $_POST['random'] );
		
		return $this->write("common::quiz:edit", "update common.question set qtext='|qtext|', answer='|answer|', 
								random_choice_order='|random_choice_order|' 
								where id = '|id|'");		
	}
	
	public function editchoice(){
		$this->bind( 'id', $_POST['cid'] );
		$this->bind( 'ctext', $_POST['ctext'] );
		
		return $this->write("common::quiz_question:edit", "update common.question_choices set ctext='|ctext|' where id='|id|'");
	}
	
	public function removequizquestion(){
		$this->bind( 'quiz_id', $_POST['quiz_id'] );
		$this->bind( 'question_id', $_POST['question_id'] );
		
		//remove question choices
		$this->write("common::question_choices:remove", "delete from common.question_choices where question_id='|question_id|'");
		
		//remove question
		return $this->write("common::quiz_question:remove", "delete from common.quiz_question where quiz_id='|quiz_id|' and question_id='|question_id|'");	
	}	
	
	public function removechoice(){
		$this->bind( 'id',  $_POST['cid'] );		
		$this->bind( 'question_id',  $_POST['question_id'] );
		
		return $this->write("common::question_choices:remove", "delete from common.question_choices where id='|id|' and question_id='|question_id|'");		
	}
	
	public function removequiz(){
		$this->bind( 'quiz_id',  $_POST['quiz_id'] );

		$this->write("common::quiz:remove", "delete from common.quiz where id='|quiz_id|'");		
		$this->write("common::quiz_question:remove", "delete from common.quiz_question where quiz_id='|quiz_id|'");
	}
}