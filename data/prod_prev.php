<?php
	header("Content-Type: application/json");

	$item 				 = strtoupper($_POST["cod_fus"]);
	$sample_a                        = strtoupper($_POST["$sample_a"]);
	$sample_b 			 = strtoupper($_POST["$sample_b"]);
	$nav_header			 = "<th>sample_a</th><th>sample_b</th><th>sample_c(d1)</th>";
	preg_match_all("/([^ 	]+)/", $sample_txt_pesq, $matches);

	foreach ($matches[1] as $match){
		$ands_spec .= " AND tbl LIKE '%".$match."%'";
		$ands_spec2 .= " AND tbl_b LIKE '%".$match."%'";
	}

	$ands_spec = preg_replace("/^[A ]*ND (.+)/", "$1)", $ands_spec);
	$ands_spec2 = preg_replace("/^[A ]*ND (.+)/", "$1)", $ands_spec2);

	if (!empty($sample_a) && $input != "btn_sample_a"){
		$ands .= " AND item LIKE '%".$sample_a."%'";
	}
	if (!empty($sampl) && $input != "btn_b"){
		$ands .= " AND bitola LIKE '%".$sample_b."%'";
   	}

	$json->erro = false;
	$json->mensagem = "";

	if (isset($_POST["adv_src"])){
		try {
			include "../admin/connect.php";

			$sql = "SELECT tbls FROM produtos WHERE ".$ands." ORDER BY unknown LIMIT 100";
			$sql_count = "SELECT COUNT(item) FROM catalogo WHERE ".$ands;
			$sql = preg_replace("/(WHERE[ ]*)AND/", "$1", $sql);
			$sql_count = preg_replace("/(WHERE[ ]*)AND/", "$1", $sql_count);
			$sql_count_res = mysqli_query($conexao, $sql_count);
			$count = mysqli_fetch_array($sql_count_res);

			if ($count[0] > 100){
				$json->mensagem_max = "Total de resultados: ".$count[0].", Limite de resultados: 100, tente refinar a pesquisa";
			}

			$json->counter = $count[0];
			$nav_produtos = "<tr><td>Produto</td><td>unknown</td><td>unknown</td></tr>";
			$result = mysqli_query($conexao, $sql);

			for($i = 0; $i < mysqli_num_rows($result); $i++){
				$row = mysqli_fetch_array($result);
				$nav_all .= "<tr><td>".$row['produto']."</td><td>".$row['unk']."</td><td>".$row['unk2']."</td></tr>";
			}

			$all_in = "<table id='table_id' class='display'><thead><tr id='table-head' style='min-width:100%'>".$nav_header."</tr>"."</thead><tbody id='table-body'>".$nav_all."</tbody></table>";
			$json->all_in = $all_in;
		} catch(Exception $e){
			$json->mensagem = $e->getMessage();
			$json->erro = true;
		}
		echo json_encode($json);
	} else {
		$string = strtoupper($_POST["postdata"]);
		try {
			include "../admin/connect.php";

			if ($_POST["pesq_set"] == "ON"){
				$sels  = "tblaa, tblb etc";
				$limit = "100";
			} else {
				$sels  = "tbl menor p cel";
				$limit = "40";

				$sql_str = "SELECT campos_textuais FROM catalogo WHERE tblcmp_txt_a LIKE '".$string."' OR tblcmp_txt_b = '".$string."' LIMIT 1";
				$sql_check = mysqli_query($conexao, $sql_str);
				if (mysqli_num_rows($sql_check) >= 1){
					$sql_full = "YES";
				}
			}

			if ($input !== "desc" && $ands_spec != ""){
				$ands_spec = preg_replace("/^/", " AND (", $ands_spec);
				$ands_spec = $ands_spec.$ands_spec2;
				$ands_spec = preg_replace("/\)tblcmp_txt_a/", " OR tblcmp_txt_a", $ands_spec);
			}

			switch ($input){
				case ("sample_a"):
					$sql = "SELECT ".$sels." FROM produto WHERE produto_ou_desc LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
					CASE
						WHEN item LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
						ELSE 2
					END LIMIT ".$limit."";
				break;
				case ("desc"):
					$sql = "SELECT ".$sels." FROM produto WHERE (".$ands_spec." OR
						(".$ands_spec2.$ands." ORDER BY
						CASE
							WHEN (desc_a LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' OR desc_b LIKE '".mb_substr($string, 0, 1, "UTF-8")."%') THEN 1
							ELSE 2
						END LIMIT ".$limit."";
				break;
				case ("bit"):
					$sql = "SELECT ".$sels." FROM produto WHERE $sample_b LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN bitola LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
			}
			if ($_POST["pesq_set"] == "OFF"){
				if ($sql_full == "YES"){
					$sql_full = "SELECT cmps FROM produto WHERE camp_a = '".$string."' OR camp_b = '".$string."'";
					$sql_full = mysqli_query($conexao, $sql_full);

					for($i = 0; $i < mysqli_num_rows($sql_full); $i++){
						$row = mysqli_fetch_array($sql_full);
						$nav_all . = "<tr><td>".$row['item'].
							     "</td><td>".$row['desc'].
							     "</td><td>".$row['desc_b']."</td></tr>";
					}

					$all_in = "<table id='table_id' class='display'><thead><tr id='table-head' style='min-width:100%'>".$nav_header."</tr>"."</thead><tbody id='table-body'>".$nav_all."</tbody></table>";
					$json->all_in = $all_in;
					$json->resc = "set";
				} else {
					$result = mysqli_query($conexao, $sql);
					$togt = mysqli_num_rows($result);

					for($i = 0; $i < $togt; $i++){
						$row = mysqli_fetch_array($result);
						$json->stringT[$i] = array(
							"string_pl" => $row["desc_abrev"],
						);
					}
				}
				echo json_encode($json);
			}
		} catch(Exception $e){
			$json->erro = true;
			$json->mensagem = $e->getMessage();
		}
	}

	if ($_POST["pesq_set"] == "ON"){
		$result_b = mysqli_query($conexao, $sql);

		for($i = 0; $i < mysqli_num_rows($result_b); $i++){
			$row = mysqli_fetch_array($result_b);
			$nav_all .= "<tr><td>".$row['item']."</td><td>".$row['desc_abrev']."</td><td>".$row['material']."</td></tr>";
		}

		$all_in = "<table id='table_id' class='display'><thead><tr id='table-head' style='min-width:100%'>".$nav_header."</tr>"."</thead><tbody id='table-body'>".$nav_all."</tbody></table>";
		$json->all_in = $all_in;
		echo json_encode($json);
	}
?>
