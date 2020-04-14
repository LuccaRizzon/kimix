<?php
	header("Content-Type: application/json");

	$item 				 = strtoupper($_POST["cod_fus"]);
	$descricao_amigaveal = strtoupper($_POST["desc"]);
	$bitola 			 = strtoupper($_POST["bit"]);
	$comprimento 		 = strtoupper($_POST["comp"]);
	$norma_dimen 		 = strtoupper($_POST["nor_dim"]);
	$norma_rosca 		 = strtoupper($_POST["nor_ros"]);
	$fpp 				 = strtoupper($_POST["fpp"]);
	$material 			 = strtoupper($_POST["mat"]);
	$input 				 = $_POST["location"];
	$nav_header			 = "<th>Item</th><th>Descrição</th><th>Bitola(d1)</th><th>Comprimento(l)</th><th>Norma Dimensões</th><th>Norma Rosca</th><th>Passo(p)</th><th>FPP</th><th>Material</th>";
	preg_match_all("/([^ 	]+)/", $descricao_amigaveal, $matches);

	foreach ($matches[1] as $match){
		$ands_spec .= " AND descricao_amigaveal LIKE '%".$match."%'";
		$ands_spec2 .= " AND descricao LIKE '%".$match."%'";
	}

	$ands_spec = preg_replace("/^[A ]*ND (.+)/", "$1)", $ands_spec);
	$ands_spec2 = preg_replace("/^[A ]*ND (.+)/", "$1)", $ands_spec2);

	if (!empty($item) && $input != "cod_fus"){
		$ands .= " AND item LIKE '%".$item."%'";
	}
	if (!empty($bitola) && $input != "bit"){
		$ands .= " AND bitola LIKE '%".$bitola."%'";
   	}
	if (!empty($comprimento) && $input != "comp"){
		$ands .= " AND comprimento LIKE '%".$comprimento."%'";
	}
	if (!empty($norma_dimen) && $input != "nor_dim"){
		$ands .= " AND norma_dimen LIKE '%".$norma_dimen."%'";
	}
	if (!empty($norma_rosca) && $input != "nor_ros"){
		$ands .= " AND norma_rosca LIKE '%".$norma_rosca."%'";
	}
	if (!empty($fpp) && $input != "fpp"){
		$ands .= " AND fpp LIKE '%".$fpp."%'";
	}
	if (!empty($material) && $input != "mat"){
		$ands .= " AND material LIKE '%".$material."%'";
	}

	$json->erro = false;
	$json->mensagem = "";

	if (isset($_POST["adv_src"])){
		try {  
			include "../admin/connect.php";

			$sql = "SELECT item, descricao_amigaveal, bitola, comprimento, norma_dimen, norma_rosca, passo, fpp, material FROM catalogo WHERE ".$ands." ORDER BY material LIMIT 100";
			$sql_count = "SELECT COUNT(item) FROM catalogo WHERE ".$ands;
			$sql = preg_replace("/(WHERE[ ]*)AND/", "$1", $sql);
			$sql_count = preg_replace("/(WHERE[ ]*)AND/", "$1", $sql_count);
			$sql_count_res = mysqli_query($conexao, $sql_count);
			$count = mysqli_fetch_array($sql_count_res);

			if ($count[0] > 100){
				$json->mensagem_max = "Total de resultados: ".$count[0].", Limite de resultados: 100, tente refinar a pesquisa";
			}

			$json->counter = $count[0];
			$nav_produtos = "<tr><td>Item</td><td>Descrição</td><td>Bitola(d1)</td><td>Comprimento(l)</td><td>Norma Dimensões</td><td>Norma Rosca</td><td>Passo(p)</td><td>FPP</td><td>Material</td></tr>";
			$result = mysqli_query($conexao, $sql);

			for($i = 0; $i < mysqli_num_rows($result); $i++){
				$row = mysqli_fetch_array($result);
				$nav_all .= "<tr><td>".$row['item']."</td><td>".$row['descricao_amigaveal']."</td><td>".$row['bitola']."</td><td>".$row['comprimento']."</td><td>".$row['norma_dimen']."</td><td>".$row['norma_rosca']."</td><td>".$row['passo']."</td><td>".$row['fpp']."</td><td>".$row['material']."</td></tr>";
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
				$sels  = "item, descricao_amigaveal, bitola, comprimento, norma_dimen, norma_rosca, passo, fpp, material";
				$limit = "100";
			} else {
				$sels  = "item, descricao, descricao_amigaveal, ordena";
				$limit = "40";

				$sql_str = "SELECT descricao_amigaveal, descricao FROM catalogo WHERE descricao_amigaveal LIKE '".$string."' OR descricao = '".$string."' LIMIT 1";
				$sql_check = mysqli_query($conexao, $sql_str);
				if (mysqli_num_rows($sql_check) >= 1){
					$sql_full = "YES";
				}
			}

			if ($input !== "desc" && $ands_spec != ""){
				$ands_spec = preg_replace("/^/", " AND (", $ands_spec);
				$ands_spec = $ands_spec.$ands_spec2;
				$ands_spec = preg_replace("/\)descricao/", " OR descricao", $ands_spec);
			}

			switch ($input){
				case ("cod_fus"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE item LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
					CASE
						WHEN item LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
						ELSE 2
					END LIMIT ".$limit."";
				break;
				case ("desc"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE (".$ands_spec." OR
						(".$ands_spec2.$ands." ORDER BY
						CASE
							WHEN (descricao_amigaveal LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' OR descricao LIKE '".mb_substr($string, 0, 1, "UTF-8")."%') THEN 1
							ELSE 2
						END LIMIT ".$limit."";
				break;
				case ("bit"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE bitola LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN bitola LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
				case ("comp"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE comprimento LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN comprimento LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
				case ("nor_dim"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE norma_dimen LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN norma_dimen LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
				case ("nor_ros"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE norma_rosca LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN norma_rosca LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
				case ("fpp"):
					$sql = "SELECT ".$sels." FROM catalogo WHERE fpp LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN fpp LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
				case ("mat"):
						$sql = "SELECT ".$sels." FROM catalogo WHERE material LIKE '%".$string."%'".$ands."".$ands_spec." ORDER BY
							CASE
								WHEN material LIKE '".mb_substr($string, 0, 1, "UTF-8")."%' THEN 1
								ELSE 2
							END LIMIT ".$limit."";
				break;
			}
			if ($_POST["pesq_set"] == "OFF"){
				if ($sql_full == "YES"){
					$sql_full = "SELECT item, descricao_amigaveal, bitola, comprimento, norma_dimen, norma_rosca, passo, fpp, material FROM catalogo WHERE descricao = '".$string."' OR descricao_amigaveal = '".$string."'";
					$sql_full = mysqli_query($conexao, $sql_full);

					for($i = 0; $i < mysqli_num_rows($sql_full); $i++){
						$row = mysqli_fetch_array($sql_full);
						$nav_all . = "<tr><td>".$row['item']."</td><td>".$row['descricao_amigaveal']."</td><td>".$row['bitola']."</td><td>".$row['comprimento']."</td><td>".$row['norma_dimen']."</td><td>".$row['norma_rosca']."</td><td>".$row['passo']."</td><td>".$row['fpp']."</td><td>".$row['material']."</td></tr>";
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
							"string_pl" => $row["descricao_amigaveal"],
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
			$nav_all .= "<tr><td>".$row['item']."</td><td>".$row['descricao_amigaveal']."</td><td>".$row['bitola']."</td><td>".$row['comprimento']."</td><td>".$row['norma_dimen']."</td><td>".$row['norma_rosca']."</td><td>".$row['passo']."</td><td>".$row['fpp']."</td><td>".$row['material']."</td></tr>";
		}

		$all_in = "<table id='table_id' class='display'><thead><tr id='table-head' style='min-width:100%'>".$nav_header."</tr>"."</thead><tbody id='table-body'>".$nav_all."</tbody></table>";
		$json->all_in = $all_in;
		echo json_encode($json);
	}
?>