<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Vector extends CI_Model {

    public function get_list($data) {
        $data=trim($data);
        $result = $this->db->select('id')
                 ->where('hsa', $data)
                 ->or_where('mi', $data)
                 ->get('list_vectors')
                 ->row_array();
        if($result) {
            return $result['id'];
        }
        return false;
    }

    public function get_lists($search) {
		$select = ($search[0] == 'h' || $search[0] == 'H') ? 'hsa' : 'mi';
		$result = [];
        $vectors = $this->db->select($select.' as hsa_mi, hsa')
                       ->like($select, $search, 'after')
			           ->limit(50)
					   ->get('list_vectors')
					   ->result_array();
		if($vectors) {
			$result['from'] = 'lists';
			$result['vectors'] = $vectors;
		} else {
			$vectors = $this->db   ->like('text', $search, 'after')
								   ->limit(50)
								   ->get('dictionary')
								   ->result_array();
			if($vectors) {
				$result['from'] = 'dictionary';
				$result['vectors'] = $vectors;
			}
		}
        return $result;
    }
	
	public function get_delimeter($list_id) {
		if(strpos($list_id, ' ')) {
			return ' ';
		}
		if(strpos($list_id, '	')) {
			return '	';
		}
		if(strpos($list_id, ',')) {
			return ',';
		}
		if(strpos($list_id, "\n")) {
			return "\n";
		}
		if(strpos($list_id, ';')) {
			return ';';
		}
		return false;
	}
	
	/**
	 * calculating dot product rank by searched keyword
	 * calculation is doing using mysql
	 * @param string $list_id
	 * @return array 
	 */
	public function dot_product_rank($list_id) {
		
		// initializing 2 strings queries for columns 1-280 and 281 - 560 because there is a limit for mysql memory
		$vectors_query1 = $vectors_query2 = ''; 
	
		for($i=1; $i<281; ++$i) {
			$vectors_query1 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}
		
		for($i=281; $i<561; ++$i) {
			$vectors_query2 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}	
		// ------------ //
		
		$vectors_query = substr($vectors_query1, 0, -1);
		// calculating dot product in mysql query for columns 1-280
		$result1 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs
									CROSS JOIN (SELECT list_vectors.* FROM list_vectors WHERE mi='".$list_id."' OR hsa='".$list_id."') as v")->result_array();


		$vectors_query = substr($vectors_query2, 0, -1);
		// calculating dot product in mysql query for columns 281-560
		$result2 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs
									CROSS JOIN (SELECT list_vectors.* FROM list_vectors WHERE mi='".$list_id."' OR hsa='".$list_id."') as v")->result_array();
	
	
		$result = [];
		
		$mi_values = [];

		// merging calculation results for columns 1-280 and 281-560
		foreach ($result1 as $key => &$row) {
			$result[$row['hsa']] = $row['dot'] + $result2[$key]['dot'];
			$mi_values[$row['hsa']] = $row['mi'];
		}

		arsort($result); // sorting result
		$result = array_slice($result, 0, 50); // getting first 50 result
		$mi_result = [];
		foreach($result as $hsa => $value) {
			$mi_result[$hsa] = $mi_values[$hsa]; // getting MI keywords for each result using key for array "hsa" label
		}
		
		return ['result' => $result, 'mi_result' => $mi_result]; // returning result
		
	}
	
	public function dot_product_rank_dictinonary($text) {
		$vectors_query1 = $vectors_query2 = '';
		
		for($i=1; $i<281; ++$i) {
			$vectors_query1 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}
		
		for($i=281; $i<561; ++$i) {
			$vectors_query2 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}
		
		
		$dictionary = $this->db->select('id')->limit(1)->where('text', $text)->get('dictionary')->row_array();
		if($dictionary) {
			$vectors_query = substr($vectors_query1, 0, -1);
			$result1 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs
										CROSS JOIN (SELECT term_vectors.* FROM term_vectors WHERE id = $dictionary[id]) as v")->result_array();
										
			$vectors_query = substr($vectors_query2, 0, -1);
			$result2 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs
										CROSS JOIN (SELECT term_vectors.* FROM term_vectors WHERE id = $dictionary[id]) as v")->result_array();
										
										
			$result = [];
		
			$mi_values = [];

			foreach ($result1 as $key => &$row) {
				$result[$row['hsa']] = $row['dot'] + $result2[$key]['dot'];
				$mi_values[$row['hsa']] = $row['mi'];
			}

			arsort($result);
			$result = array_slice($result, 0, 50);
			$mi_result = [];
			foreach($result as $hsa => $value) {
				$mi_result[$hsa] = $mi_values[$hsa];
			}
			
			return ['result' => $result, 'mi_result' => $mi_result];
		}
		
		return [];
		
		
	}


	public function dot_product_term($list_id) {
		$vectors_query1 = $vectors_query2 = '';

		for($i=1; $i<281; ++$i) {
			$vectors_query1 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}

		for($i=281; $i<561; ++$i) {
			$vectors_query2 .= 'v.c'.$i.' * vs.c'.$i.' +';
		}


		//$dictionary = $this->db->select('id')->limit(1)->where('text', $list_id)->get('dictionary')->row_array();

			$vectors_query = substr($vectors_query1, 0, -1);
			$result1 = $this->db->query("SELECT vs.id, d.text, ($vectors_query) AS dot FROM term_vectors as vs
										CROSS JOIN (SELECT list_vectors.* FROM list_vectors WHERE mi='".$list_id."' OR hsa='".$list_id."') as v
										INNER JOIN dictionary as d ON d.id = vs.id 
										")->result_array();

			$vectors_query = substr($vectors_query2, 0, -1);
			$result2 = $this->db->query("SELECT vs.id, d.text, ($vectors_query) AS dot FROM term_vectors as vs
										CROSS JOIN (SELECT list_vectors.* FROM list_vectors WHERE mi='".$list_id."' OR hsa='".$list_id."') as v
										INNER JOIN dictionary as d ON d.id = vs.id
										")->result_array();




		$result = [];

		foreach ($result1 as $key => &$row) {
			$result[$row['text']] = $row['dot'] + $result2[$key]['dot'];
		}
		arsort($result);
		return array_slice($result, 0, 300);
	}
	
	public function get_abstracts($hsa) {
		$vector = $this->db->select('id,hsa')->where('hsa', $hsa)->limit(1)->get('list_vectors')->row_array();
		if($vector) {
			$result = array();
			$result['abstract'] = $this->db	->select('a.text')
											->join('list_abstract as l', 'l.abstract_id = a.id')
											->where('l.list_id', $vector['id'])
											->get('abstracts as a')
											->result_array();

			$result['key'] = substr($vector['hsa'],4);
			return $result;
		}
		return [];
		
	}



	public function get_common_abstracts($list_labels) {
		$result = [];
		$list_id_result = $this->db->select('id,hsa')->where_in('hsa', $list_labels)->get('list_vectors')->result_array();
		$list_ides = [];
		foreach ($list_id_result as $list_id) {
			$list_ides[] = $list_id['id'];
		}
		$abstracts = [];
		foreach ($list_ides as $list_id) {
			$abstract = [];
			$abstracts_r = $this->db->select('abstract_id')->where('list_id', $list_id)->get('list_abstract')->result_array();
			foreach ($abstracts_r as $abs_id) {
				$abstract[] = $abs_id['abstract_id'];
			}
			$abstracts[] = $abstract;
		}

		if(count($abstracts) > 1) {
			$abstracts_ides = call_user_func_array('array_intersect', $abstracts);
			if($abstracts_ides) {
				$result['abstract'] = $this->db->select('text')->where_in('id', $abstracts_ides)->get('abstracts')->result_array();
				$result['key'] = $list_id_result;
			}
		}
		return $result;
	}
	
	public function get_nodes_connections($node_names) {
		$result = [];
		foreach($node_names as $node) {
			$result[$node] = [];
			foreach($node_names as $s_node) {
				if($this->get_common_abstracts([$node, $s_node])) {
					$result[$node][] = $s_node;
				}
			}
		}
		return $result;
	}
	
	public function dot_product_rank_multi($search, $delimeter) {
		$list_ides = explode($delimeter, $search);
		$total = count($list_ides);
		if($total > 1) {
			
			$list_sources = [];
			$list_vectors = [];
			$term_vectors = [];
			
			foreach($list_ides as $list_id) {
				if($this->db->where('hsa', $list_id)->or_where('mi', $list_id)->count_all_results('list_vectors')) {
					$list_sources[$list_id] = 'list_vectors';
					$list_vectors[] = $list_id;
					
				} elseif($this->db->where('text', $list_id)->count_all_results('dictionary')) {
					$list_sources[$list_id] = 'term_vectors';
					$term_vectors[] = $list_id;
					
				} else {
					return false;
				}
			}
			$select = '';
			$from = '';
			$where = '';
			
			for($i=1; $i<561; ++$i) {
				$sub_q1 = '';
				$j = 1;
				for($j=1; $j<=$total; $j++) {
					$sub_q1 .= "v$j.c$i + ";
				}
				$sub_q1 = substr($sub_q1, 0, -2);
				$sub_q1 .= "as X$i, ";
				$select .= $sub_q1;
			}
			$select = substr($select, 0, -2);
			
			$j = 1;
			foreach($list_sources as $list_id => $source) {
				$from .= "$source as v$j, ";
				$j++;
			}
			$from = substr($from, 0, -2);
			
			$j = 1;
			foreach($list_sources as $list_id => $source) {
				$where .= $source == 'list_vectors' ? "(v$j.hsa = '$list_id' OR v$j.mi = '$list_id') AND " : "(v$j.id = (SELECT id from dictionary WHERE text = '$list_id')) AND ";
				$j++;
			}
			
			$where = substr($where, 0, -4);
			
			$query = "
				SELECT $select FROM $from 
				WHERE $where
			";
			
			$X_vector = $this->db->query($query)->row_array();
			$sum = 0;
			foreach($X_vector as $num) {
				$sum += $num * $num;
			}
			$N = sqrt($sum);
			
			foreach($X_vector as &$num) {
				$num = $num / $N;
			}
			
			
			$vectors_query1 = $vectors_query2 = '';
	
			for($i=1; $i<281; ++$i) {
				$vectors_query1 .= '('.$X_vector['X'.$i].') * vs.c'.$i.' +';
			}
			
			for($i=281; $i<561; ++$i) {
				$vectors_query2 .= $X_vector['X'.$i].' * vs.c'.$i.' +';
			}	
			
			$vectors_query = substr($vectors_query1, 0, -1);
			$result1 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs")->result_array();


			$vectors_query = substr($vectors_query2, 0, -1);

			$result2 = $this->db->query("SELECT vs.hsa, vs.mi, ($vectors_query) AS dot FROM list_vectors as vs")->result_array();
		
		
			$result = [];
		
			$mi_values = [];

			foreach ($result1 as $key => &$row) {
				$result[$row['hsa']] = $row['dot'] + $result2[$key]['dot'];
				$mi_values[$row['hsa']] = $row['mi'];
			}

			arsort($result);
			$result = array_slice($result, 0, 50);
			$mi_result = [];
			foreach($result as $hsa => $value) {
				$mi_result[$hsa] = $mi_values[$hsa];
			}			
			
			
			return compact('result', 'mi_result', 'list_vectors', 'term_vectors');
			
			
		}
		return false;
		
	}
	
	public function higlight_key($key, $text) {
		if(strpos($key, 'hsa-') === false) {
			$key = 'hsa-'.$key;
		}
		$text = str_replace($key, "<span>".$key."</span>", $text);
		$text = str_replace($key, "<span>".$key."</span>", $text);
		
		$search_text = str_replace('hsa-', '', $key);
		$text = str_replace($search_text, "<span>".$search_text."</span>", $text);
		
		$search_text = str_replace('mir', 'miR', $search_text);
		$text = str_replace($search_text, "<span>".$search_text."</span>", $text);
		
		$search_text = str_replace('miR', 'miRNA', $search_text);
		$text = str_replace($search_text, "<span>".$search_text."</span>", $text);
		
		$exploded = explode('-', $key);
		if(count($exploded) > 3) {
			unset($exploded[count($exploded)-1]);
			unset($exploded[0]);
			$key = implode('-', $exploded);
			$text = str_replace($key, "<span>".$key."</span>", $text);
			
			$search_text = str_replace('mir', 'miR', $key);
			$text = str_replace($search_text, "<span>".$search_text."</span>", $text);
		}
		$text = str_replace('hsa-<span>', "<span>hsa-", $text);
		return $text;
	}
	
	public function higlight_expression_key($key, $text) {
		//echo $key; die;
		$text = str_ireplace(' '.$key, " <span style='background-color:green; color:#fff'>".$key."</span>", $text);
		return $text;
	}

}