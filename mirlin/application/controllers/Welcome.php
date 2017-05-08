<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {



	public function __construct() {
		parent::__construct();
		$this->load->model('vector');
	}

	public function index()
	{
		$this->load->view('header');
		$this->load->view('content');
		$this->load->view('footer');
	}
	
	public function test()
	{
		$this->load->view('header');
		$this->load->view('content2');
		$this->load->view('footer');
	}

	/**
	 * shows dot product result
	 * @return mixed 
	 */
	public function dot_product() {
		$list_id = $this->input->get('list'); // search keyword
		$button = $this->input->get('search_in'); //rank button or rank_and_term button was clicked
		$delimeter = $this->vector->get_delimeter($list_id); // getting delimeter for multiple search
		
		if(!$delimeter) { // if only one keyword was searched 
			$from = $this->input->get('from');
			if(!$from) {
				$from = 'lists';
			}
			if($from == 'lists') { // if search keyword is from list_vectors
				$result = $this->vector->dot_product_rank($list_id); // calculating dot_product_rank 
				if($button == 'term'){ 
					$term_result = $this->vector->dot_product_term($list_id); // calculating dot_product_term 
				}
			} else { // if search keyword is from dictionary
				$result = $this->vector->dot_product_rank_dictinonary($list_id); // calculating dot_product_rank_dictinonary
			}
			
		} else {
			$result = $this->vector->dot_product_rank_multi($list_id, $delimeter); // calculating dot_product_rank for multi search
		}
		
		$this->load->view('header');
		$this->load->view('content', compact('result', 'term_result'));
		$this->load->view('footer');
	}

	/**
	 * shows autocomplete result for search
	 * @return mixed 
	 */
	public function ajax_search($search_word)
	{
		if($result = $this->vector->get_lists($search_word)) {
			$key = $result['from'] == 'lists' ? 'hsa_mi' : 'text';
			echo '<input type="hidden" id="from_type" name="from" value="',$result['from'],'" />';
			foreach ($result['vectors'] as $vector) {
				echo '<a class="link-clas"><div class="show" align="left"><span class="name">'.$vector[$key].'</span></div></a>';
			}
		}
	}
	
	public function ajax_search2($search_word)
	{
		$json_arr = [];
		if($result = $this->vector->get_lists($search_word)) {
			$key = $result['from'] == 'lists' ? 'hsa_mi' : 'text';
			//echo '<input type="hidden" id="from_type" name="from" value="',$result['from'],'" />';
			foreach ($result['vectors'] as $vector) {
				$json_arr[] = [
					'name' => $vector[$key]
				];
			}
				echo json_encode([
				'from' => '<input type="hidden" id="from_type" name="from" value="'.$result['from'].'" />',
				'vectors' => $json_arr
			]);
		} else {
			echo json_encode(['vectors' => false]);
		}
		
	}
	
	
	public function get_abstracts($hsa, $expression_keys = []) {
		$abstracts = $this->vector->get_abstracts($hsa);
		if($abstracts) {
			echo '<h1> Abstracts (', count($abstracts['abstract']), ')</h1>';
			foreach($abstracts['abstract'] as $abstract) {
				$title = explode('.',$abstract['text']);
				$title = $title[0].".";
				$title_link = '<a class="abstract_title" href="#">' . $title . '</a>';
				$text = str_replace(array($title,""), "", $abstract['text']);
				
				$text = $this->vector->higlight_key($abstracts['key'], $text);
				foreach($expression_keys as $key) {
					$text = $this->vector->higlight_expression_key($key, $text);
				}
				
				echo $title_link.'<p>', $text , '</p> <br />';
			}
		} else {
			echo '<h1>Abstracts not found</h1>';
		}
		
	}

	public function get_common_abstracts(){
		$expression_keys = [];
		$list_ides = $this->input->post('list');
		foreach($list_ides as $key => $list_id) {
			if($list_id[0] != 'h') {
				$expression_keys[] = $list_id;
				unset($list_ides[$key]);
			}
		}
		if(count($list_ides) == 1) {
			foreach($list_ides as $list_id) {
				return $this->get_abstracts($list_id, $expression_keys);
			}
		}
		$list = $this->vector->get_common_abstracts($list_ides);
		if($list) {
			echo '<h1> Abstracts (', count($list['abstract']), ')</h1>';
			foreach($list['abstract'] as $abstract) {
				$title = explode('.', $abstract['text']);
				$title = $title[0] . ".";
				$title_link = '<a class="abstract_title" href="#">' . $title . '</a>';
				$text = str_replace(array($title, ""), "", $abstract['text']);
				foreach($list['key'] as $keys){

					$text = $this->vector->higlight_key($keys['hsa'], $text);
					foreach($expression_keys as $key) {
						$text = $this->vector->higlight_expression_key($key, $text);
					}
				}
				echo $title_link . '<p>', $text, '</p> <br />';
			}
		} else {
			echo '<h1>Abstracts not found</h1>';
		}

	}
	
	public function get_nodes_connections() {
		if($node_names = $this->input->post('node_names')) {
			$nodes_connections = $this->vector->get_nodes_connections($node_names);
			echo json_encode($nodes_connections);
		}
	}
	
	public function export_csv() {
		$json_data = $this->input->post('export_data');
		if($json_data) {
			if($data = json_decode($json_data)) {
				// output headers so that the file is downloaded rather than displayed
				header('Content-Type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename=data.csv');

				// create a file pointer connected to the output stream
				$output = fopen('php://output', 'w');

				// output the column headings
				fputcsv($output, ['Search keyword', $data->search]);
				fputcsv($output, ['-----', '----']);
				fputcsv($output, ['Label', 'dot']);
				foreach($data->data as $vector) {
					fputcsv($output, [$vector->label, $vector->dot]);
				}
				
				if(isset($data->term_data)) {
					foreach($data->term_data as $vector) {
						fputcsv($output, [$vector->label, $vector->dot]);
					}
				}
			}
			
		}
		
	}
	
	public function insert_text(){
		/*
		error_reporting(-1);
		set_time_limit(0);
		
		
		$this->load->dbforge();
    $fields = [];
    $this->dbforge->add_key('id', TRUE);
    $fields['id'] =  [
        'type' => 'INT',
        'constraint' => 10,
        'unsigned' => TRUE,
        'auto_increment' => TRUE,
        'primary' => TRUE,
    ];

    $fields['mi'] =  [
        'type' => 'VARCHAR',
        'constraint' => 32,
    ];

    $fields['hsa'] =  [
        'type' => 'VARCHAR',
        'constraint' => 32,
    ];

    for($i=1; $i<561; $i++) {
        $fields['c'.$i] = [
            'type' => 'DOUBLE'
        ];
    }

    $this->dbforge->add_field($fields);
    $this->dbforge->create_table('list_vectors');
		
		
		$f = fopen('text_files/CI_task/PMID_mapping.txt', 'r');
		$j = 0;
		$total = 0;
		while($row = fgets($f)) {
			$j++;
			$abstracts = explode(';', $row);
			
			$ab1 = explode('	', $abstracts[0]);
			$abstracts[0] = $ab1[3]; 
			unset($abstracts[count($abstracts)-1]);
			$insert_batch = [];
			foreach($abstracts as $ab_name) {
				$ab_file = fopen('text_files/CI_task/abstracts_files/abstracts/'.$ab_name.'.txt', 'r');
				$ab_content = trim(fgets($ab_file));
				$insert_batch[] = [
					'list_id' => $j,
					'text' => $ab_content
				];
			}
			$total +=  count($insert_batch);
			$this->db->insert_batch('abstracts', $insert_batch);
		}
		die;
		
		
		
		/*		
		$f = fopen('text_files/CI_task/DICTIONARY.txt', 'r');
		$insert_batch = [];
		$j = 0;
		while($row = fgets($f)) {
			$insert_batch[] = ['text' => trim($row)];
			$j++;
			if($j > 1000) {
				$this->db->insert_batch('dictionary', $insert_batch);
				$j = 0;
				$insert_batch = [];
			}
		}
		$this->db->insert_batch('dictionary', $insert_batch);
		/*$f = fopen('text_files/CI_task/term_vectors.txt', 'r');
		$insert_batch = [];
		$j = 1;
		$total = 0;
		while($row = fgets($f)) {
			$total++;
			if($total < 63001) {
				continue;
			}
			if($total > 70000) {
				break;
			}
			$vector = explode(' ', $row);
			$filter_vector = [];
			$i = 1;
			foreach($vector as $value) {
				if($value = doubleval(trim($value))) {
					$filter_vector['c'.$i++] = $value;
				}
			}
			$insert_batch[] = $filter_vector;
			$j++;
			if($j > 400) {
				$this->db->insert_batch('term_vectors', $insert_batch);
				$j = 0;
				$insert_batch = [];
			}
		}

		$this->db->insert_batch('term_vectors', $insert_batch);*/
	}
	/*public function insert_abstract(){
		$abstracts = $this->db->query('SELECT DISTINCT text FROM abstracts1')->result_array();
		foreach($abstracts as $abstract){
			$this->db->set('text', $abstract['text']);
			$this->db->insert('abstracts');
		}
	}
	public function list_abstract(){
		set_time_limit(0);
		$abstracts1 = $this->db->query('SELECT * FROM abstracts WHERE id >10000')->result_array();

		foreach($abstracts1 as $abs){
			$abstract = $this->db->query('SELECT * FROM abstracts1 WHERE text="'.addslashes($abs['text']).'"')->result_array();
			foreach($abstract as $abstr){
			$this->db->set('list_id', $abstr['list_id']);
			$this->db->set('abstract_id', $abs['id']);
			$this->db->insert('list_abstract');
			}
		}
	}*/
}
