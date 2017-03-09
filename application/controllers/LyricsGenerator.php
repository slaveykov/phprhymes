<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class LyricsGenerator extends CI_Controller {

	public function index()
	{
		$result = '';
		
		if(isset($_POST['add_lyric'])){
			
			$lyric = $_POST['lyric'];
			$last_word = explode(" ", $lyric);
			$last_word = end($last_word);
			
			$this->insert_lyric($lyric, $last_word);
		}
		
		$result = $this->generate_lyrics();
		
		$this->load->view('lyrics_generator',array("result"=>$result));
	}
	
	private function insert_lyric($lyric, $last_word){
	
		$this->db->where('content', $lyric);
		$this->db->from('izrecheniq');
			
		if($this->db->count_all_results()==0){
			
			$data = array(
				'content' => $lyric,
				'last_word' => $last_word
			);
	
			$this->db->insert('izrecheniq', $data);
		}
	
	}
	
	private function generate_lyrics(){
		
		$this->db->insert('generator', array("content"=>json_encode(array("used_words"=>array()))));
		$generator_id = $this->db->insert_id();
	
		
		echo $generator_id;
		
		$return = '';
		$i=0;
		while (true) {
			
			if($i>=4){
				break;
			}
			
			$query = $this->db->from('generator')->where('id', $generator_id)->get();
			$row_generator = $query->row_array();
			
			$query = $this->db->from('izrecheniq')->order_by('rand()')->limit(1)->get();
			$row = $query->row_array();
			$random_word  = trim($row['last_word']);
			
			$rhyme = $this->searchRhymes($random_word,$row['id']);
			
			if(empty($rhyme['content'])){
				continue;
			}
			
			$json = json_decode($row_generator['content'], TRUE);
			$used_words = $json['used_words'];
			
			if(in_array($row['id'], $used_words)){
				continue;
			}
			if(in_array($rhyme['id'], $used_words)){
				continue;
			}
			
			array_push($used_words, $row['id']);
			array_push($used_words, $rhyme['id']);
			
			$update_array = array();
			$update_array['used_words'] = $used_words;
			
			$this->db->update("generator", array("content"=>json_encode($update_array)), array("id"=>$generator_id));
			
			$return .= $row['content'].'<br />';
			$return .= $rhyme['content'];
			
			$return .= "<br /><br />";
			$i++;
		}
		
		return $return;
	}
	
	private function searchRhymes($word,$id){
	
		$return = '';
	
		$best_rhymes = array();
		$second_rhymes = array();
	
		$query = $this->db->from('izrecheniq')->where("id != $id", NULL)->get();
	
		if(strlen(utf8_decode($word))>=4){
			$word_last_four = mb_substr($word, -4);
		}
		if(strlen(utf8_decode($word))>=3){
			$word_last_three = mb_substr($word, -3);
		}
		$i=0;
		foreach ($query->result_array() as $row)
		{
			if(isset($word_last_four)){
				if(strlen(utf8_decode($row['last_word']))>=4){
					$last_four = mb_substr($row['last_word'], -4);
					if($word_last_four == $last_four){
						if($row['last_word']!=$word){
							$best_rhymes[$i]['content'] = $row['content'];
							$best_rhymes[$i]['id'] = $row['id'];
						}
					}
				}
			}
			if(isset($word_last_three)){
				if(strlen(utf8_decode($row['last_word']))>=3){
					$last_three = mb_substr($row['last_word'], -3);
					if($word_last_three == $last_three){
						if($row['last_word']!=$word){
							if(in_array($row['last_word'],$best_rhymes)==false){
								$second_rhymes[$i]['content'] = $row['content'];
								$second_rhymes[$i]['id'] = $row['id'];
							}
						}
					}
				}
			}
			$i++;
		}
		
		if(!empty($best_rhymes)){
			$num = array_rand($best_rhymes);	
			return array("content"=>$best_rhymes[$num]['content'], "id"=>$best_rhymes[$num]['id']);
		}
		
		if(!empty($second_rhymes)){
			$num = array_rand($second_rhymes);
			return array("content"=>$second_rhymes[$num]['content'], "id"=>$second_rhymes[$num]['id']);
		}
		
		return array();
	}
}