<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function index()
	{
		
		set_time_limit(0);
		
		$result = '';
		
		if(isset($_POST['search_word'])){
			$word = $_POST['word'];
			$result .= $this->insert_word($word);
			$result .=  'We searching rhymes for: <h1>'.$word.'</h1>';	
			$result .= $this->searchRhymes($word);
		}
		
		if(isset($_POST['add_words'])){
			
			$words = $_POST['words'];
			$words = str_replace(',', '', $words);
			$words = str_replace('.', '', $words);
			$words = str_replace('?', '', $words);
			$words = str_replace('"', '', $words);
			$words = mb_strtolower($words);
			$words = explode(" ", $words);
			$new_words = array();
			foreach($words as $word){
				if(!empty(explode(PHP_EOL, $word))){
					foreach(explode(PHP_EOL, $word) as $word_two){
						$new_words[] = $word_two;
					}
				}else{
					$new_words[] = $word;
				}
			}
			$words = array();
			foreach($new_words as $word){
				if(strlen(utf8_decode($word))>=3){
					if(in_array($word, $words)){
						continue;
					}
					$words[] = $word;
				}
			}
			foreach($words as $word){
				$this->insert_word($word);
			}
		}
		
		$this->load->view('home',array("result"=>$result));
		
	}
	public function apiDownload(){

		$this->load->helper('url');
		
		$word_uri = urldecode($this->uri->segment(3));
		$word = urlencode($word_uri);
		
		$query2 = $this->db->from('rimichka_com')->where('content', $word_uri)->get();
		$row2 = $query2->result_array();
		
		$query = $this->db->from('words')->order_by('rand()')->limit(1)->get();
		$row = $query->row_array();
		
		
		if(!empty($row2)){
			redirect("http://localhost/phprhymes/index.php/Home/apiDownload/".$row['content']);
			die();
		}
		
		$this->insert_word2($word_uri);
		
		
		$url = "http://rimichka.com/?word=$word&json=1";
		$json = json_decode(file_get_contents($url),TRUE);
		
		foreach($json as $j){ 
			$word = $j['wrd'];
			$this->insert_word($word);
		}
		
		$random = $row['content'];
		$random = str_replace("(", "", $random);
		$random = str_replace(")", "", $random);
		$random = str_replace("!", "", $random);
		$random = str_replace(";", "", $random);
		$random = str_replace(".", "", $random);
		$random = str_replace(",", "", $random);
		$random = str_replace("*", "", $random);
		$random = preg_replace('/\PL+/u', '', $random);
		
		//sleep(1);
		//redirect("http://localhost/phprhymes/index.php/Home/apiDownload/$random");
		echo '<meta http-equiv="refresh" content="0;URL=http://localhost/phprhymes/index.php/Home/apiDownload/'.$random.'" />';
		
	}
	private function searchRhymes($word){
		
		$return = '';
		
		$best_rhymes = array();
		$second_rhymes = array();
		
		$query = $this->db->get('words');
		
		if(strlen(utf8_decode($word))>=4){
			$word_last_four = mb_substr($word, -4);
		}
		if(strlen(utf8_decode($word))>=3){
			$word_last_three = mb_substr($word, -3);
		}
		
		foreach ($query->result_array() as $row)
		{
			if(isset($word_last_four)){
				if(strlen(utf8_decode($row['content']))>=4){
					$last_four = mb_substr($row['content'], -4);
					if($word_last_four == $last_four){
						if($row['content']!=$word){
							$best_rhymes[] = $row['content'];
						}
					}
				}
			}
			if(isset($word_last_three)){
				if(strlen(utf8_decode($row['content']))>=3){
					$last_three = mb_substr($row['content'], -3);
					if($word_last_three == $last_three){
						if($row['content']!=$word){
							if(in_array($row['content'],$best_rhymes)==false){
								$second_rhymes[] = $row['content'];
							}
						}
					}
				}
			}
		}
		if(!empty($best_rhymes)){
			$return .= 'BEST RHYMES:<br />';
		}
		$return .= '<div style="width:700px;word-wrap: break-word;font-size:19px;font-weight:bold;">';
		foreach($best_rhymes as $rhymes){
			$return .= ''.$rhymes.', ';
		}
		$return .= '</div>';
		if(!empty($best_rhymes)){
			$return .= '<br />SECOND RHYMES:<br />';
		}
		$return .= '<div style="width:700px;word-wrap: break-word;font-size:18px;">';
		foreach($second_rhymes as $rhymes){
			$return .= ''.$rhymes.', ';
		}
		$return .= '</div>';
		
		return $return;
	}
	
	private function insert_word($word){
		
		$this->db->where('content', $word);
		$this->db->from('words');
			
		if($this->db->count_all_results()==0){
			$data = array(
				'content' => $word
			);
				
		$this->db->insert('words', $data);
		}
		
	}
	
	private function insert_word2($word){
	
		$this->db->where('content', $word);
		$this->db->from('rimichka_com');
			
		if($this->db->count_all_results()==0){
			$data = array(
					'content' => $word
			);
	
			$this->db->insert('rimichka_com', $data);
		}
	
	}
}
