<?php
use PayU\ApplePay\ApplePayDecodingServiceFactory;
use PayU\ApplePay\ApplePayValidator;
use MatthiasMullie\Minify;

require_once("./include/calc_senddate.php");
require_once 'vendor/autoload.php';
require_once ("./config/ApiConfig.php");
require_once 'include/cc/ccDraw.php';
require_once 'config/apple_pay/apple_pay_conf.php';

class Process
{
    const ITEM_PATH = 'template/cached_files/smart_more_%s.html';

	static function downloadCsvBlankItemStatistic() {
		global $sql;
		$startDate = Globals::get('start_date');
		$endDate = Globals::get('end_date');

		$contents = '"item_type","item_type_size","item_type_sub","count"'."\n";
		$fields = ["item_type", "item_type_size","item_type_sub","count"];
		$filename = "無地のみのCSV_" .$startDate . "_" . $endDate;

		$arr = [];
		$tmp_table = "pay_item";
		$tmp_where = $sql->setWhere($tmp_table, null, "regist_unix", ">=", strtotime($startDate));
		$tmp_where = $sql->setWhere($tmp_table, $tmp_where, "regist_unix", "<", strtotime(str_replace('-', '/', $endDate) . "+1 days"));
		$tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "=", "blank");
		$tmp_order = $sql->setOrder($tmp_table, null, 'regist_unix', 'asc');

		$result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

		while($rec = $sql->sql_fetch_assoc($result))
		{
			$pay = $sql->selectRecord('pay', $rec['pay']);
			if($pay['delivery_state'] == 1) {
				if(isset($arr[$rec['item_type']][$rec['item_type_size']][$rec['item_type_sub']])) {
					$arr[$rec['item_type']][$rec['item_type_size']][$rec['item_type_sub']] += $rec['item_row'];
				} else {
					$arr[$rec['item_type']][$rec['item_type_size']][$rec['item_type_sub']] = $rec['item_row'];
				}
			}
		}

		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

		$f = fopen("php://output", 'w');
		fputcsv($f, $fields);

		foreach ($arr as $keyItemType => $itemType) {
			foreach ($itemType as $keyItemTypeSize => $itemTypeSize) {
				foreach ($itemTypeSize as $keyItemTypeSub => $count) {
					$contents .= '"'.$keyItemType.'","'.$keyItemTypeSize.'","'.$keyItemTypeSub.
						'","'.$count.'"';
					$contents .= "\n";
					$lineData = [
						$keyItemType,$keyItemTypeSize,$keyItemTypeSub,$count
					];
					fputcsv($f, $lineData);
				}
			}
		}

		ob_flush(); // dump buffer
		fclose($f);
		die();
	}

	static function csvProc()
	{

		global $sql;
		global $cc;
        ini_set('memory_limit', '200M');
        ini_set('max_execution_time', 0);

		switch(Globals::get("type"))
		{
			case 'user':
			case 'user_fee':
			case 'pay':
				$table = Globals::get("type");
				$sys = SystemUtil::createSystem($table);
				$search = new Search($table);
				$search_tmp = $sys->searchProc($table, $search);
				$result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
				break;
			case 'pay_item':
                //Find pay under conditions
                if (!empty(Globals::get('list'))) {
                    $pay_query = Globals::get('list');
                } else {
                    $pay_table = 'pay';
                    $pay_sys = SystemUtil::createSystem($pay_table);
                    $pay_search = new Search($pay_table);
                    $pay_search_tmp = $pay_sys->searchProc($pay_table, $pay_search);

                    $pay_query = $sql->getSelectResult($pay_table, $pay_search_tmp["where"], $pay_search_tmp["order"], null, [['name' => 'id']], null, null, null, true);
                }

				$table = Globals::get("type");
				$sys = SystemUtil::createSystem($table);
				$search = new Search($table);
				$search_tmp = $sys->searchProc($table, $search);
				break;
            case 'promotion_code':
                $table = Globals::get("type");
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProc($table, $search);
                $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
                break;
            case 'seo_setting':
                //Find item under conditions
                $table = Globals::get("type");
                if (!empty(Globals::get('list'))) {
                    $item_list = Globals::get('list');
                    $result = sprintf('SELECT * FROM item WHERE `item`.id IN (%s)', $item_list);
                } else {
                    $sys = SystemUtil::createSystem();
                    $search = new Search($table);
                    $search_tmp = $sys->searchProc($table, $search);
                    $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"], null, null, true);
                }
                break;
			default:
				return;
		}

		switch(Globals::session("LOGIN_TYPE"))
		{
			case 'admin':

				switch($table)
				{
					case 'user':

						$filename = "会員情報_".date("Y_m_d").".csv";
						$contents = 'ニックネーム' . "\t" . 'メールアドレス' . "\n";

						while($rec = $sql->sql_fetch_assoc($result))
						{
							$rec = TextUtil::arrayReplace($rec, '"', '""');

							$contents .= '"=""' . $rec["name"] . '"""' . "\t" . '"=""' . $rec["mail"] . '"""';
							$contents .= "\n";
						}
						break;

					case 'user_fee':
						$filename = "報酬支払明細_".date("Y_m_d").".csv";
						$contents = '販売者' . "," . '振込先（銀行名）' . "," . '振込先（支店名）' . "," .
							'振込先（口座種別）' . "," . '振込先（口座番号）' . "," . '振込先（口座名義）' .
							"," . '明細' . "," . '精算金額' . "," . 'ステータス' . "\n";
						$state = array(0 => '未払い', 1 => '支払済み', 2 => 'その他');
						$bank_u_type = array('ordinary' => '普通', 'current' => '当座' );

						while($rec = $sql->sql_fetch_assoc($result))
						{
							$rec = TextUtil::arrayReplace($rec, '"', '""');

							if(!$user_rec = $sql->selectRecord("user", $rec["user"]))
							{
								$user_rec["name"] = "－";
								$user_rec["bank_name"] = "－";
								$user_rec["bank_branch"] = "－";
								$user_rec["bank_u_type"] = "－";
								$user_rec["bank_u_num"] = "－";
								$user_rec["bank_u_name"] = "－";
							}

						$contents .= '"' . $user_rec["name"] . '"' . "," . '"' . $user_rec["bank_name"] . '"' .
								"," . '"' . $user_rec["bank_branch"] . '"' . "," . '"' . $bank_u_type[$user_rec["bank_u_type"]] .
								'"' . "," . '"' . $user_rec["bank_u_num"] . '"' . "," . '"' . $user_rec["bank_u_name"] .
								'"' . "," . '"' . $rec["subject"] . '"' . "," . '"' . ($rec["fee_total"] + $rec["discount"]) .
								'"' . "," . '"' . $state[$rec["state"]] . '"';
							$contents .= "\n";
						}
						break;

					case 'pay':
						if(Globals::get('zip') == 1) {
							$dir = 'attachments/';
							$data = $result;
							$tomsContents = self::prepareDownloadCsvToms($data);
							$cabContents = self::prepareDownloadCsvCab($data);
							$felicContents = self::prepareDownloadCsvFelic($data);
                            $bonmaxContents = self::prepareDownloadCsvBonmax($data);

							$pathCab = fopen($dir.'cab.csv', 'w');
							fputs($pathCab, $cabContents);
							fclose($pathCab);

							$pathFelic = fopen($dir.'felic.csv', 'w');
							fputs($pathFelic, $felicContents);
							fclose($pathFelic);

							$pathToms = fopen($dir.'toms.csv', 'w');
                            fputs($pathToms, $tomsContents);
                            fclose($pathToms);

                            $pathBonmax = fopen($dir.'bonmax.csv', 'w');
                            fputs($pathBonmax, $bonmaxContents);
                            fclose($pathBonmax);

							jsonEncode(['msg' => 'download file successful']);
							exit;
						}

						if(Globals::get("target")=="cab"){
							$filename = "cab注文履歴_".date("Y_m_d").".csv";

							$contents = self::prepareDownloadCsvCab($result);
						} elseif (Globals::get("target")=="felic") {
							$filename = "felic注文履歴_".date("Y_m_d").".csv";

							$contents = self::prepareDownloadCsvFelic($result);
						} elseif (Globals::get("target")=="toms") {
                            $filename = "toms注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvToms($result);
                        }elseif (Globals::get("target")=="bonmax") {
                            $filename = "bonmax注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvBonmax($result);
                        }
						break;
					case 'pay_item':
                        $where = '';
                        if(!empty($pay_query)){
                            $where .= sprintf('WHERE pay_item.pay IN (%s)',$pay_query);
                        }
						foreach ($search_tmp['where'] as $val) {
							if($val['name'] == 'regist_unix' && $val['operator'] == 'BETWEEN') {
								$where .= 'AND regist_unix between ' . $val['value'][0] . ' and ' .  $val['value'][1];
							}
						}
						$query = 'SELECT
								master_item_type.item_code,
								master_item_type_sub.`name` as color,
								master_item_type_size.`name` as size,
								count( * ) as total
							FROM pay_item
								INNER JOIN master_item_type ON pay_item.item_type = master_item_type.id
								INNER JOIN master_item_type_sub ON pay_item.item_type_sub = master_item_type_sub.id
								INNER JOIN master_item_type_size ON pay_item.item_type_size = master_item_type_size.id';
						if(isset($where)) $query .= " $where ";
						$query .= ' GROUP BY
								master_item_type.item_code,
								master_item_type_sub.`name`,
								master_item_type_size.`name`';

						if($query) {
							$result = $sql->queryRaw($table, $query);
						}
						$filename = date('YmdHi').".csv";
						$contents = '"アイテムコード","カラー","サイズ","合計"'."\n";
						while($rec = $sql->sql_fetch_assoc($result))
						{
							$rec = TextUtil::arrayReplace($rec, '"', '""');

							$item_code = (string)$rec['item_code'];
							$item_color = $rec['color'];
							$item_size = $rec['size'];
							$total = $rec['total'];

							$contents .= '"' . $item_code . '","' . $item_color . '","' . $item_size . '","' . $total . '"';
							$contents .= "\n";
						}
						break;
                    case 'promotion_code':

                        $filename = "クーポンコード".date("Y-m-d").".csv";
                        $contents = 'コード' . "," . 'コード名' . "," . '発行日' . "," . '有効期限' . "," . '割引金額' . "," . 'ステータス' . "," . '注文番号' . "\n";

                        while($rec = $sql->sql_fetch_assoc($result))
                        {
                            $rec = TextUtil::arrayReplace($rec, '"', '""');
                            if (!empty($rec["pay"])) {
                                $pay = $sql->selectRecord('pay',$rec["pay"]);
                            }
                            switch ($rec["state"]) {
                                case '0':
                                    $status = '未使用';
                                    break;
                                case '2':
                                    $status = '使用済';
                                    break;
                                case '3':
                                    $status = '期限切れ';
                                    break;
                                default:
                                    $status = 'undefined';
                            }
                            $contents .= $rec["code"] . "," . '"' . $rec["name"] . '"' . "," . date("Y-m-d",$rec["regist_unix"]) . "," . date("Y-m-d",$rec["expire"]) . "," . $rec["discount"] . "," . $status . "," . $pay["pay_num"];
                            $contents .= "\n";
                        }
                        break;
                    case 'seo_setting':
                        if ($result) {
                            $result = $sql->queryRaw($table, $result);
                        }
                        $item_id = array();
                        $filename = "楽天モール用" . date("Y-m-d") . ".csv";
                        $contents = 'コントロールカラム' . "," . '商品管理番号（商品URL）' . "," . '商品番号' . "," . '全商品ディレクトリID' . "," . 'タグID' . "," . 'PC用キャッチコピー' . "," . 'モバイル用キャッチコピー' . "," . '商品名' . "," . '販売価格' . "," . '表示価格' . "," . '消費税' . "," . '送料' . "," . '個別送料' . "," . '送料区分1' . "," . '送料区分2' . "," . '代引料' . "," . '倉庫指定' . "," . '商品情報レイアウト' . "," . '注文ボタン' . "," . '資料請求ボタン' . "," . '商品問い合わせボタン' . "," . '再入荷お知らせボタン' . "," . 'のし対応' . "," . 'PC用商品説明文' . "," . 'スマートフォン用商品説明文' . "," . 'PC用販売説明文' . "," . '商品画像URL' . "," . '商品画像名（ALT）' . "," . '動画' . "," . '販売期間指定' . "," . '注文受付数' . "," . '在庫タイプ' . "," . '在庫数' . "," . '在庫数表示' . "," . '項目選択肢別在庫用横軸項目名' . "," . '項目選択肢別在庫用縦軸項目名' . "," . '項目選択肢別在庫用残り表示閾値' . "," . 'RAC番号' . "," . 'サーチ非表示' . "," . '闇市パスワード' . "," . 'カタログID' . "," . '在庫戻しフラグ' . "," . '在庫切れ時の注文受付' . "," . '在庫あり時納期管理番号' . "," . '在庫切れ時納期管理番号' . "," . '予約商品発売日' . "," . 'ポイント変倍率' . "," . 'ポイント変倍率適用期間' . "," . 'ヘッダー・フッター・レフトナビ' . "," . '表示項目の並び順' . "," . '共通説明文（小）' . "," . '目玉商品' . "," . '共通説明文（大）' . "," . 'レビュー本文表示' . "," . 'あす楽配送管理番号' . "," . '海外配送管理番号' . "," . 'サイズ表リンク' . "," . '医薬品説明文' . "," . '医薬品注意事項' . "," . '二重価格文言管理番号' . "," . 'カタログIDなしの理由' . "," . '配送方法セット管理番号' . "," . '白背景画像URL' . "," . 'メーカー提供情報表示' . "," . '地域別個別送料管理番号' . "," . '消費税率' . "\n";
                        while ($rec = $sql->sql_fetch_assoc($result)) {
                            $item_preview = '';
                            $item_id[] = '"' . $rec["id"] . '"';
                            $rec = TextUtil::arrayReplace($rec, '"', '""');

                            if (!empty($rec["flag_preview_item"]) && !empty($rec["item_preview" . $rec["flag_preview_item"]])) {
                                $item_preview = $rec["item_preview" . $rec["flag_preview_item"]];
                            }

                            $contents .= sprintf('"n","%s",,551180,,,,"%s","%s",,0,0,,,,0,,,,,,,,"%s","%s",,"%s",,,,-1,2,,,"サイズ",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,',$rec["id"],$rec["name"],$rec["price"],$rec["item_text"],$rec["item_text"],$item_preview);
                            $contents .= "\n";
                        }

                        if (!empty($item_id)) {
                            $sql->rawQuery(sprintf('UPDATE item SET rakuten_exported = 1 WHERE id IN (%s)',  implode(",", $item_id)));
                        }
                        break;
				}
				break;
		}

		//$filename = mb_convert_encoding($filename, "SHIFT_JIS", "UTF-8");
		//$contents = chr(255) . chr(254) . mb_convert_encoding($contents, 'UTF-16LE', 'UTF-8');
		$contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

		ob_end_clean();
		ob_start();

		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		echo $contents;

		ob_end_flush();
		exit;

		//HttpUtil::download($filename, $contents);
	}

    static function csvProcPasral()
    {

        global $sql;
        global $cc;

        switch(Globals::getTableGlobal())
        {
            case 'user':
            case 'user_fee':
            case 'pay':
                $table = Globals::getTableGlobal();
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProc($table, $search);
                $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
                break;
            case 'pay_item':
                //Find pay under conditions
                if (!empty(Globals::get('list'))) {
                    $pay_query = Globals::get('list');
                } else {
                    $pay_table = 'pay';
                    $pay_sys = SystemUtil::createSystem($pay_table);
                    $pay_search = new Search($pay_table);
                    $pay_search_tmp = $pay_sys->searchProc($pay_table, $pay_search);

                    $pay_query = $sql->getSelectResult($pay_table, $pay_search_tmp["where"], $pay_search_tmp["order"], null, [['name' => 'id']], null, null, null, true);
                }

                $table = Globals::getTableGlobal();
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProc($table, $search);
                break;
            case 'promotion_code':
                $table = Globals::get("type");
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProc($table, $search);
                $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
                break;
            case 'seo_setting':
                //Find item under conditions
                $table = Globals::getTableGlobal();
                if (!empty(Globals::get('list'))) {
                    $item_list = Globals::get('list');
                    $result = sprintf('SELECT * FROM item WHERE `item`.id IN (%s)', $item_list);
                } else {
                    $sys = SystemUtil::createSystem();
                    $search = new Search($table);
                    $search_tmp = $sys->searchProc($table, $search);
                    $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"], null, null, true);
                }
                break;
            default:
                return;
        }

        switch(Globals::session("LOGIN_TYPE"))
        {
            case 'admin':

                switch($table)
                {
                    case 'user':

                        $filename = "会員情報_".date("Y_m_d").".csv";
                        $contents = 'ニックネーム' . "\t" . 'メールアドレス' . "\n";

                        while($rec = $sql->sql_fetch_assoc($result))
                        {
                            $rec = TextUtil::arrayReplace($rec, '"', '""');

                            $contents .= '"=""' . $rec["name"] . '"""' . "\t" . '"=""' . $rec["mail"] . '"""';
                            $contents .= "\n";
                        }
                        break;

                    case 'user_fee':
                        $filename = "報酬支払明細_".date("Y_m_d").".csv";
                        $contents = '販売者' . "," . '振込先（銀行名）' . "," . '振込先（支店名）' . "," .
                            '振込先（口座種別）' . "," . '振込先（口座番号）' . "," . '振込先（口座名義）' .
                            "," . '明細' . "," . '精算金額' . "," . 'ステータス' . "\n";
                        $state = array(0 => '未払い', 1 => '支払済み', 2 => 'その他');
                        $bank_u_type = array('ordinary' => '普通', 'current' => '当座' );

                        while($rec = $sql->sql_fetch_assoc($result))
                        {
                            $rec = TextUtil::arrayReplace($rec, '"', '""');

                            if(!$user_rec = $sql->selectRecord("user", $rec["user"]))
                            {
                                $user_rec["name"] = "－";
                                $user_rec["bank_name"] = "－";
                                $user_rec["bank_branch"] = "－";
                                $user_rec["bank_u_type"] = "－";
                                $user_rec["bank_u_num"] = "－";
                                $user_rec["bank_u_name"] = "－";
                            }

                            $contents .= '"' . $user_rec["name"] . '"' . "," . '"' . $user_rec["bank_name"] . '"' .
                                "," . '"' . $user_rec["bank_branch"] . '"' . "," . '"' . $bank_u_type[$user_rec["bank_u_type"]] .
                                '"' . "," . '"' . $user_rec["bank_u_num"] . '"' . "," . '"' . $user_rec["bank_u_name"] .
                                '"' . "," . '"' . $rec["subject"] . '"' . "," . '"' . ($rec["fee_total"] + $rec["discount"]) .
                                '"' . "," . '"' . $state[$rec["state"]] . '"';
                            $contents .= "\n";
                        }
                        break;

                    case 'pay':
                        if(Globals::get('zip') == 1) {
                            $dir = 'attachments/';
                            $data = $result;
                            $tomsContents = self::prepareDownloadCsvToms($data);
                            $cabContents = self::prepareDownloadCsvCab($data);
                            $felicContents = self::prepareDownloadCsvFelic($data);
                            $bonmaxContents = self::prepareDownloadCsvBonmax($data);

                            $pathCab = fopen($dir.'cab.csv', 'w');
                            fputs($pathCab, $cabContents);
                            fclose($pathCab);

                            $pathFelic = fopen($dir.'felic.csv', 'w');
                            fputs($pathFelic, $felicContents);
                            fclose($pathFelic);

                            $pathToms = fopen($dir.'toms.csv', 'w');
                            fputs($pathToms, $tomsContents);
                            fclose($pathToms);

                            $pathBonmax = fopen($dir.'bonmax.csv', 'w');
                            fputs($pathBonmax, $bonmaxContents);
                            fclose($pathBonmax);

                            jsonEncode(['msg' => 'download file successful']);
                            exit;
                        }

                        if(Globals::get("target")=="cab"){
                            $filename = "cab注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvCab($result);
                        } elseif (Globals::get("target")=="felic") {
                            $filename = "felic注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvFelic($result);
                        } elseif (Globals::get("target")=="toms") {
                            $filename = "toms注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvToms($result);
                        }elseif (Globals::get("target")=="bonmax") {
                            $filename = "bonmax注文履歴_".date("Y_m_d").".csv";

                            $contents = self::prepareDownloadCsvBonmax($result);
                        }
                        break;
                    case 'pay_item':
                        $where = '';
                        if(!empty($pay_query)){
                            $where .= sprintf('WHERE pay_item.pay IN (%s)',$pay_query);
                        }
                        foreach ($search_tmp['where'] as $val) {
                            if($val['name'] == 'regist_unix' && $val['operator'] == 'BETWEEN') {
                                $where .= 'AND regist_unix between ' . $val['value'][0] . ' and ' .  $val['value'][1];
                            }
                        }
                        $query = 'SELECT
								master_item_type.item_code,
								master_item_type_sub.`name` as color,
								master_item_type_size.`name` as size,
								count( * ) as total
							FROM pay_item
								INNER JOIN master_item_type ON pay_item.item_type = master_item_type.id
								INNER JOIN master_item_type_sub ON pay_item.item_type_sub = master_item_type_sub.id
								INNER JOIN master_item_type_size ON pay_item.item_type_size = master_item_type_size.id';
                        if(isset($where)) $query .= " $where ";
                        $query .= ' GROUP BY
								master_item_type.item_code,
								master_item_type_sub.`name`,
								master_item_type_size.`name`';

                        if($query) {
                            $result = $sql->queryRaw($table, $query);
                        }
                        $filename = date('YmdHi').".csv";
                        $contents = '"アイテムコード","カラー","サイズ","合計"'."\n";
                        while($rec = $sql->sql_fetch_assoc($result))
                        {
                            $rec = TextUtil::arrayReplace($rec, '"', '""');

                            $item_code = (string)$rec['item_code'];
                            $item_color = $rec['color'];
                            $item_size = $rec['size'];
                            $total = $rec['total'];

                            $contents .= '"' . $item_code . '","' . $item_color . '","' . $item_size . '","' . $total . '"';
                            $contents .= "\n";
                        }
                        break;
                    case 'promotion_code':

                        $filename = "クーポンコード".date("Y-m-d").".csv";
                        $contents = 'コード' . "," . 'コード名' . "," . '発行日' . "," . '有効期限' . "," . '割引金額' . "," . 'ステータス' . "," . '注文番号' . "\n";

                        while($rec = $sql->sql_fetch_assoc($result))
                        {
                            $rec = TextUtil::arrayReplace($rec, '"', '""');
                            if (!empty($rec["pay"])) {
                                $pay = $sql->selectRecord('pay',$rec["pay"]);
                            }
                            switch ($rec["state"]) {
                                case '0':
                                    $status = '未使用';
                                    break;
                                case '2':
                                    $status = '使用済';
                                    break;
                                case '3':
                                    $status = '期限切れ';
                                    break;
                                default:
                                    $status = 'undefined';
                            }
                            $contents .= $rec["code"] . "," . '"' . $rec["name"] . '"' . "," . date("Y-m-d",$rec["regist_unix"]) . "," . date("Y-m-d",$rec["expire"]) . "," . $rec["discount"] . "," . $status . "," . $pay["pay_num"];
                            $contents .= "\n";
                        }
                        break;
                    case 'seo_setting':
                        if ($result) {
                            $result = $sql->queryRaw($table, $result);
                        }
                        $item_id = array();
                        $filename = "楽天モール用" . date("Y-m-d") . ".csv";
                        $contents = 'コントロールカラム' . "," . '商品管理番号（商品URL）' . "," . '商品番号' . "," . '全商品ディレクトリID' . "," . 'タグID' . "," . 'PC用キャッチコピー' . "," . 'モバイル用キャッチコピー' . "," . '商品名' . "," . '販売価格' . "," . '表示価格' . "," . '消費税' . "," . '送料' . "," . '個別送料' . "," . '送料区分1' . "," . '送料区分2' . "," . '代引料' . "," . '倉庫指定' . "," . '商品情報レイアウト' . "," . '注文ボタン' . "," . '資料請求ボタン' . "," . '商品問い合わせボタン' . "," . '再入荷お知らせボタン' . "," . 'のし対応' . "," . 'PC用商品説明文' . "," . 'スマートフォン用商品説明文' . "," . 'PC用販売説明文' . "," . '商品画像URL' . "," . '商品画像名（ALT）' . "," . '動画' . "," . '販売期間指定' . "," . '注文受付数' . "," . '在庫タイプ' . "," . '在庫数' . "," . '在庫数表示' . "," . '項目選択肢別在庫用横軸項目名' . "," . '項目選択肢別在庫用縦軸項目名' . "," . '項目選択肢別在庫用残り表示閾値' . "," . 'RAC番号' . "," . 'サーチ非表示' . "," . '闇市パスワード' . "," . 'カタログID' . "," . '在庫戻しフラグ' . "," . '在庫切れ時の注文受付' . "," . '在庫あり時納期管理番号' . "," . '在庫切れ時納期管理番号' . "," . '予約商品発売日' . "," . 'ポイント変倍率' . "," . 'ポイント変倍率適用期間' . "," . 'ヘッダー・フッター・レフトナビ' . "," . '表示項目の並び順' . "," . '共通説明文（小）' . "," . '目玉商品' . "," . '共通説明文（大）' . "," . 'レビュー本文表示' . "," . 'あす楽配送管理番号' . "," . '海外配送管理番号' . "," . 'サイズ表リンク' . "," . '医薬品説明文' . "," . '医薬品注意事項' . "," . '二重価格文言管理番号' . "," . 'カタログIDなしの理由' . "," . '配送方法セット管理番号' . "," . '白背景画像URL' . "," . 'メーカー提供情報表示' . "," . '地域別個別送料管理番号' . "," . '消費税率' . "\n";
                        while ($rec = $sql->sql_fetch_assoc($result)) {
                            $item_preview = '';
                            $item_id[] = '"' . $rec["id"] . '"';
                            $rec = TextUtil::arrayReplace($rec, '"', '""');

                            if (!empty($rec["flag_preview_item"]) && !empty($rec["item_preview" . $rec["flag_preview_item"]])) {
                                $item_preview = $rec["item_preview" . $rec["flag_preview_item"]];
                            }

                            $contents .= sprintf('"n","%s",,551180,,,,"%s","%s",,0,0,,,,0,,,,,,,,"%s","%s",,"%s",,,,-1,2,,,"サイズ",,,,,,,,,,,,,,,,,,,,,,,,,,,,,,,',$rec["id"],$rec["name"],$rec["price"],$rec["item_text"],$rec["item_text"],$item_preview);
                            $contents .= "\n";
                        }

                        if (!empty($item_id)) {
                            $sql->rawQuery(sprintf('UPDATE item SET rakuten_exported = 1 WHERE id IN (%s)',  implode(",", $item_id)));
                        }
                        break;
                }
                break;
        }

        //$filename = mb_convert_encoding($filename, "SHIFT_JIS", "UTF-8");
        //$contents = chr(255) . chr(254) . mb_convert_encoding($contents, 'UTF-16LE', 'UTF-8');
        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        echo $contents;

        ob_end_flush();
        exit;

        //HttpUtil::download($filename, $contents);
    }

	static function prepareDownloadCsvCab($data,$changegarment=false) {
		global $sql;

		$contents = '"品番","カラー","サイズ","数量","お客様注文Ｎｏ．"'."\n";

		while($rec = $sql->sql_fetch_assoc($data))
		{
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
			$rec = TextUtil::arrayReplace($rec, '"', '""');

			$tmp_table = "pay_item";
			$tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
			}
			$tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
			$tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

			while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
			{
				$master_item_type = "";
				$master_item_type_size = "";
				$master_item_type_sub = "";

                if (in_array($tmp_rec['item_type_size'], CLONE_SIZES)) {
                    $tmp_rec["item_type"] = ORIGINAL_ID;
                } elseif (in_array($tmp_rec['item_type_size'],
                    ['ITSI7296', 'ITSI7297', 'ITSI7298', 'ITSI7299', 'ITSI7300', 'ITSI7301', 'ITSI7302', 'ITSI7303'])) {
                    $tmp_rec["item_type"] = 'IT368';
                }

				if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = $tmp2_rec["vendor_item_code"];$vendor_id = $tmp2_rec["vendor_id"];
				if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $master_item_type_size = $tmp2_rec["vendor_size_code"];
				if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $master_item_type_sub = $tmp2_rec["vendor_color_code"];
				if($vendor_id!=2){
					continue;
				}
				$bikou = $rec["name"];
				$contents .= '"'.$master_item_type.'","'.$master_item_type_sub.'","'.$master_item_type_size.
					'","'.$tmp_rec["item_row"].'","'.$rec["pay_num"].'"';
				$contents .= "\n";
			}
		}

		return $contents;
	}

	static function prepareDownloadCsvFelic($data,$changegarment=false) {
		global $sql;

		$contents="";
		while($rec = $sql->sql_fetch_assoc($data))
		{
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
			$rec = TextUtil::arrayReplace($rec, '"', '""');

			$tmp_table = "pay_item";
			$tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
			}
			$tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
			$tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

			while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
			{
				$master_item_type = "";
				$master_item_type_size = "";
				$master_item_type_sub = "";

				if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = $tmp2_rec["vendor_item_code"];$vendor_id = $tmp2_rec["vendor_id"];
				if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $master_item_type_size = $tmp2_rec["vendor_size_code"];
				if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $master_item_type_sub = $tmp2_rec["vendor_color_code"];
				if($vendor_id!=6){
					continue;
				}
				//								$bikou = "配送先氏名：".$rec["name"]."様\n".'注文番号：'.$rec["pay_num"];
				$bikou = $rec["name"];
				$contents .= '"'.$master_item_type.'","'.$master_item_type_sub.'","'.$master_item_type_size.
					'","'.$tmp_rec["item_row"].'","'.$rec["pay_num"].'"';
				$contents .= "\n";
			}
		}

		return $contents;
	}

	static function prepareDownloadCsvToms($data,$changegarment=false) {
		global $sql;

		$contents = '"品番(5桁)","カラーコード","サイズコード","数量","OPP袋同送数","備考（納品書・出荷案内書の行備考）","お客様注文Ｎｏ．"'."\n";

		while($rec = $sql->sql_fetch_assoc($data))
		{
		    if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
			$rec = TextUtil::arrayReplace($rec, '"', '""');

			$tmp_table = "pay_item";
			$tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
			}
			$tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
			$tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

			while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
			{
				$master_item_type = "";
				$master_item_type_size = "";
				$master_item_type_sub = "";

                if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = explode('-', $tmp2_rec["vendor_item_code"])[0];$vendor_id = $tmp2_rec["vendor_id"];

				if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $master_item_type_size = $tmp2_rec["item_code"];
				if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $master_item_type_sub = $tmp2_rec["item_code"];
				if($vendor_id!=1){
					continue;
				}
				//								$bikou = "配送先氏名：".$rec["name"]."様\n".'注文番号：'.$rec["pay_num"];
//				$bikou = $rec["name"];
				$bikou = "";
				$contents .= '"'.$master_item_type.'","'.$master_item_type_sub.'","'.$master_item_type_size.
					'","'.$tmp_rec["item_row"].'","0","'.$rec["pay_num"].'","'.$bikou.'"';
				$contents .= "\n";
			}
		}

		return $contents;
	}

    static function prepareDownloadCsvBonmax($data,$changegarment=false) {
        global $sql;

        $contents = '"品番","カラー","サイズ","個数","明細摘要"'."\n";

        while($rec = $sql->sql_fetch_assoc($data))
        {
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
            $rec = TextUtil::arrayReplace($rec, '"', '""');

            $tmp_table = "pay_item";
            $tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
			}
            $tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
            $tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

            while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
            {
                $master_item_type = "";
                $master_item_type_size = "";
                $master_item_type_sub = "";

                if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = $tmp2_rec["vendor_item_code"];$vendor_id = $tmp2_rec["vendor_id"];
                if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $master_item_type_size = $tmp2_rec["vendor_size_code"];
                if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $master_item_type_sub = $tmp2_rec["vendor_color_code"];
                if($vendor_id!=10){
                    continue;
                }
                $bikou = "";
                $contents .= '"'.$master_item_type.'","'.$master_item_type_sub.'","'.$master_item_type_size.
                    '","'.$tmp_rec["item_row"].'","'.$rec["pay_num"].'"';;
                $contents .= "\n";
            }
        }

        return $contents;
    }

    static function prepareDownloadCsvCapsuleBox($data,$changegarment=false) {
        global $sql;

        $contents = '"品番","数量"'."\n";

        while($rec = $sql->sql_fetch_assoc($data))
        {
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
            $rec = TextUtil::arrayReplace($rec, '"', '""');

            $tmp_table = "pay_item";
            $tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
            }
            $tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
            $tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

            while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
            {
                $master_item_type = "";

                if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = $tmp2_rec["vendor_item_code"];$vendor_id = $tmp2_rec["vendor_id"];
                if($vendor_id!=16){
                    continue;
                }
                $contents .= '"'.$master_item_type.'","'.$tmp_rec["item_row"].'"';
                $contents .= "\n";
            }
        }

        return $contents;
    }

	static function downloadZipCsv(){
		global $sql;
        ini_set('memory_limit','200M');
		$dir = 'attachments/';

		$contents = array('cab', 'felic', 'toms','bonmax','wundou','tradework','capsule');
		$count = 0;

		foreach ($contents as $content) {
			$table = Globals::get("type");
			$sys = SystemUtil::createSystem($table);
			$search = new Search($table);
			$search_tmp = $sys->searchProc($table, $search);
			$result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
			$changegarment = Globals::get("changegarment");
			switch ($content) {
				case 'cab':
					$cabContents = self::prepareDownloadCsvCab($result,$changegarment);
					$cabContents = mb_convert_encoding($cabContents, "SHIFT_JIS", "UTF-8");
					$pathCab = fopen($dir.'cab.csv', 'w');
					fputs($pathCab, $cabContents);
					fclose($pathCab);
					$count++;
					break;
				case 'felic':
					$felicContents = self::prepareDownloadCsvFelic($result,$changegarment);
					$felicContents = mb_convert_encoding($felicContents, "SHIFT_JIS", "UTF-8");
					$pathFelic = fopen($dir.'felic.csv', 'w');
					fputs($pathFelic, $felicContents);
					fclose($pathFelic);
					$count++;
					break;
				case 'toms':
					$tomsContents = self::prepareDownloadCsvToms($result,$changegarment);
					$tomsContents = mb_convert_encoding($tomsContents, "SHIFT_JIS", "UTF-8");
					$pathToms = fopen($dir.'toms.csv', 'w');
					fputs($pathToms, $tomsContents);
					fclose($pathToms);
					$count++;
					break;
                case 'bonmax':
                    $bonmaxContents = self::prepareDownloadCsvBonmax($result,$changegarment);
                    $bonmaxContents = mb_convert_encoding($bonmaxContents, "SHIFT_JIS", "UTF-8");
                    $pathBonmax = fopen($dir.'bonmax.csv', 'w');
                    fputs($pathBonmax, $bonmaxContents);
                    fclose($pathBonmax);
                    $count++;
                    break;
                case 'wundou':
                    $wundouContents = self::prepareDownloadCsvWundou($result, $changegarment);
                    $wundouContents = mb_convert_encoding($wundouContents, "SHIFT_JIS", "UTF-8");
                    $pathWundou = fopen($dir . 'wundou.csv', 'w');
                    fputs($pathWundou, $wundouContents);
                    fclose($pathWundou);
                    $count++;
                    break;
                case 'tradework':
                    $tradeworkContents = self::prepareDownloadCsvTradeWorks($result, $changegarment);
                    $tradeworkContents = mb_convert_encoding($tradeworkContents, "SHIFT_JIS", "UTF-8");
                    $pathTradework = fopen($dir . 'tradework.csv', 'w');
                    fputs($pathTradework, $tradeworkContents);
                    fclose($pathTradework);
                    $count++;
                    break;
                case 'capsule':
                    $capsuleContents = self::prepareDownloadCsvCapsuleBox($result, $changegarment);
                    $capsuleContents = mb_convert_encoding($capsuleContents, "SHIFT_JIS", "UTF-8");
                    $pathCapsule = fopen($dir . 'capsulebox.csv', 'w');
                    fputs($pathCapsule, $capsuleContents);
                    fclose($pathCapsule);
                    $count++;
                    break;
			}
			if($count == 7) {
				jsonEncode($data['msg'] = 'OK');
			}
		}
	}

    static function downloadZipCsvPasral(){
        global $sql;
        ini_set('memory_limit','200M');
        $dir = 'attachments/';

        $contents = array('cab', 'felic', 'toms','bonmax');
        $count = 0;

        foreach ($contents as $content) {
            $table = Globals::getTableGlobal();
            $sys = SystemUtil::createSystem($table);
            $search = new Search($table);
            $search_tmp = $sys->searchProc($table, $search);
            $result = $sql->getSelectResult($table, $search_tmp["where"], $search_tmp["order"], null, $search_tmp["clume"], $search_tmp["group"]);
            $changegarment = Globals::get("changegarment");
            switch ($content) {
                case 'cab':
                    $cabContents = self::prepareDownloadCsvCab($result,$changegarment);
                    $cabContents = mb_convert_encoding($cabContents, "SHIFT_JIS", "UTF-8");
                    $pathCab = fopen($dir.'cab.csv', 'w');
                    fputs($pathCab, $cabContents);
                    fclose($pathCab);
                    $count++;
                    break;
                case 'felic':
                    $felicContents = self::prepareDownloadCsvFelic($result,$changegarment);
                    $felicContents = mb_convert_encoding($felicContents, "SHIFT_JIS", "UTF-8");
                    $pathFelic = fopen($dir.'felic.csv', 'w');
                    fputs($pathFelic, $felicContents);
                    fclose($pathFelic);
                    $count++;
                    break;
                case 'toms':
                    $tomsContents = self::prepareDownloadCsvToms($result,$changegarment);
                    $tomsContents = mb_convert_encoding($tomsContents, "SHIFT_JIS", "UTF-8");
                    $pathToms = fopen($dir.'toms.csv', 'w');
                    fputs($pathToms, $tomsContents);
                    fclose($pathToms);
                    $count++;
                    break;
                case 'bonmax':
                    $bonmaxContents = self::prepareDownloadCsvBonmax($result,$changegarment);
                    $bonmaxContents = mb_convert_encoding($bonmaxContents, "SHIFT_JIS", "UTF-8");
                    $pathBonmax = fopen($dir.'bonmax.csv', 'w');
                    fputs($pathBonmax, $bonmaxContents);
                    fclose($pathBonmax);
                    $count++;
                    break;
            }
            if($count == 4) {
                jsonEncode($data['msg'] = 'OK');
            }
        }
    }

	static function csvUserInfo()
	{
		ini_set('memory_limit', '200m');
		global $sql;
		global $cc;

		switch(Globals::get("type"))
		{
			case 'pay':
				$table = Globals::get("type");
				$sys = SystemUtil::createSystem($table);
				$search = new Search($table);
				$search_tmp = $sys->searchProc($table, $search);
				break;
			default:
				return;
		}
		if(Globals::session("LOGIN_TYPE") == 'admin')
		{
			if($table == 'pay')
			{
				$filename = date('YmdHi').".csv";
				$fields = array("注文番号","アイテムid","アイテム名","アイテムタイプ","アイテムサイズ","　アイテム価格",
					"名前","住所","メールアドレス","会社名","購入日時","トータル金額","ギフト価格","ギフトピンク",
					"ギフトブルー","ギフトイエロー","サイドチチ料","サイドチチテキスト","アッパー チップ料",
					"アッパー チップテキスト","チチカラー料","チチカラーテキスト","変形カット料","変形カットテキスト");

				if($search_tmp['where']) {
					foreach ($search_tmp['where'] as $val) {
						if($val['name'] == 'pay_total' && $val['operator'] == 'BETWEEN') {
							$where = 'WHERE pay_total BETWEEN ' . $val['value'][0] . ' AND ' .  $val['value'][1];
						}

                        if ($val['name'] == 'conf_datetime') {
                            if (empty($where)) {
                                $where = sprintf('WHERE conf_datetime %s "%s"',$val['operator'], $sql->escape($val['value']));
                            } else {
                                $where .= sprintf(' AND conf_datetime %s "%s"',$val['operator'], $sql->escape($val['value']));
                            }
                        }
					}
				}
			}
		}

		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment; filename="'.$filename.'"');

		$f = fopen("php://output", 'w');
		fputcsv($f, $fields);

		$offset = 0;
		while(true){
			$query = 'SELECT *, master_item_type_size.NAME AS item_size 
							FROM
								(
							SELECT
								pay.company,
								pay.`name` as user_name,
								pay.add_sub,
								pay.mail,
								pay.pay_num,
								pay.conf_datetime,
								pay.pay_total,
								pay.gift_price,
								pay.gift_pink,
								pay.gift_blue,
								pay.gift_yellow,
								pay_item.item,
								pay.regist_unix,
								pay_item.item_name,
								pay_item.item_type,
								pay_item.item_type_size,
								pay_item.item_price,
								pay_item.side_chinchi_fee,
								pay_item.side_chinchi_text,
								pay_item.upper_tip_fee,
								pay_item.upper_tip_text,
								pay_item.chichi_color_fee,
								pay_item.chichi_color_text,
								pay_item.deformation_cut_fee,
								pay_item.deformation_cut_text
							FROM
								pay
								LEFT JOIN pay_item ON pay.id = pay_item.pay ';
			if(empty($where)){
                $where = "WHERE is_pasral != 1 AND pay_type != 'reward_market'";
            }else{
                $where = $where." AND is_pasral != 1  AND pay_type != 'reward_market'";
            }
            $query .= $where;
			$query .= ' ) as tbl1 LEFT JOIN master_item_type_size ON tbl1.item_type_size = master_item_type_size.id order by regist_unix DESC limit 1000 offset ' . $offset;

			$offset += 1000;

			$result = $sql->queryRaw($table, $query);
			while ($rec = $sql->sql_fetch_assoc($result)) {
				$lineData = [
					$rec['pay_num'], $rec['item'], $rec['item_name'], $rec['item_type'],
					$rec['item_size'], $rec['item_price'], $rec['item_name'], $rec['add_sub'],
					$rec['mail'], $rec['company'], $rec['conf_datetime'], $rec['pay_total'],
					$rec['gift_price'], $rec['gift_pink'], $rec['gift_blue'], $rec['gift_yellow'],
					$rec['side_chinchi_fee'], $rec['side_chinchi_text'], $rec['upper_tip_fee'], $rec['upper_tip_text'],
					$rec['chichi_color_fee'], $rec['chichi_color_text'], $rec['deformation_cut_fee'], $rec['deformation_cut_text']
				];

				fputcsv($f, $lineData);
			}
			if($result->num_rows < 1000) {
				break;
			}
		}
		exit;
	}

    static function csvSurvey()
    {
        global $sql;
        ini_set('memory_limit','200M');

        switch(Globals::get("type"))
        {
            case 'survey_answers':
                $table = Globals::get("type");
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProc($table, $search);
                break;
            default:
                return;
        }
        if(Globals::session("LOGIN_TYPE") == 'admin')
        {
            if($table == 'survey_answers')
            {
                $filename = 'アンケート_'.date('YmdHi').".csv";
                $fields = array("ユーザ名", "都道府県","質問の内容","ユーザーのフィードバック","アンケートに回答した日付");

                if($search_tmp['where']) {
                    foreach ($search_tmp['where'] as $val) {
                        if($val['name'] == 'regist_unix' && $val['operator'] == 'BETWEEN') {
                            $where = 'WHERE s.regist_unix BETWEEN ' . $val['value'][0] . ' AND ' .  $val['value'][1];
                        }

                        if ($val['name'] == 'answer') {
                            if (empty($where)) {
                                $where = sprintf('WHERE s.answer %s "%s"',$val['operator'], $sql->escape($val['value']));
                            } else {
                                $where .= sprintf(' AND s.answer %s "%s"',$val['operator'], $sql->escape($val['value']));
                            }
                        }
                    }
                }
            }
        }

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $f = fopen("php://output", 'w');
        fputcsv($f, $fields);

        $offset = 0;
        while(true){
            $query = getQuerySurvey();
            if(isset($where) && !empty($where)) {
                $query .= $where;
            }
            $query .= ' LIMIT 1000 offset '.$offset;
            $offset += 1000;
            $result = $sql->queryRaw($table, $query);
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $feeback = ($rec['answer'] == 1) ? 'ある' : 'ない';
                $lineData = [
                    $rec['user_name'],$rec['add_pre'], '1.Up-TのテレビCMを見たことはありますか？', $feeback, date('Y年m月d日', $rec['regist_unix']),
                ];

                fputcsv($f, $lineData);
            }
            if($result->num_rows < 1000) {
                break;
            }
        }
        exit;
    }

    static function csvWillmailList()
    {
        ini_set('memory_limit', '200m');
        global $sql;
        global $cc;
        $order_date_A = '';
        $order_date_B = '';
        $filename = 'メール一覧のダウンロード_'.date('YmdHi').".csv";
        $fields = array("メールアドレス");
        $table = 'user';
        $data = Globals::request();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $f = fopen("php://output", 'w');
        fputcsv($f, $fields);
        $offset = 0;
        while(true){
            if(isset($data['willmail_list_id']) && $data['willmail_list_id']) {
                $query = Globals::getQueryWillmailTarget($data['willmail_list_id']);
            }else {
                $query = Globals::setQueryWillMail($data);
            }
            $query .= ' LIMIT 1000 offset '.$offset;

            $offset += 1000;

            $result = $sql->rawQuery($query);
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $lineData = [
                    $rec['user_email'],
                ];
                fputcsv($f, $lineData);
            }
            if($result->num_rows < 1000) {
                break;
            }
        }
        exit;
    }

    static function updateWillmailTarget()
    {
        global $sql;
        $request = Globals::request();
        $result = '';
        if(isset($request['email']) && isset($request['id'])) {
            $update = $sql->setData('willmail_target', null, "email", $request['email']);
            $sql->updateRecord('willmail_target', $update, $request['id']);
            $result = $request['email'];
        }
        jsonEncode($result);
    }

    static function setFormatDateSearch($y, $m, $d)
    {
        $date = '';
        if(!empty($y) && !empty($m) && !empty($d)) {
            $date = $y.'-'.$m.'-'.$d;
        }
        return $date;
    }

    static function createListWillmail()
    {
        global $sql;
        $table = 'willmail_list';
        $table2 = 'willmail_target';
        $param = Globals::request();
        $result = false;
        if($param) {
            $id = SystemUtil::getUniqId($table, false, true);
            $data = $sql->setData($table, null, 'id', $id);
            $data = $sql->setData($table, $data, 'db_target_id', $param['db_target_id']);
            $data = $sql->setData($table, $data, 'condition', $param['condition']);
            $data = $sql->setData($table, $data, 'url', $param['url']);
            $data = $sql->setData($table, $data, 'name', $param['name']);
            $test = $sql->addRecord($table, $data);
            $result = true;

            $url_components = parse_url($param['url']);
            parse_str($url_components['query'], $params);
            $query = Globals::setQueryWillMail($params);
            $result2 = $sql->rawQuery($query);
            foreach ($result2 as $key => $value) {
                $data = $sql->setData($table2, null, 'willmail_list_id', $id);
                $data = $sql->setData($table2, $data, 'email', $value['user_email']);
                $sql->addRecord($table2, $data);
            }
        }
        jsonEncode($result);
    }

    static function csvUserInfoPasral()
    {
        ini_set('memory_limit', '200m');
        global $sql;
        global $cc;

        switch(Globals::getTableGlobal())
        {
            case 'pay':
                $table = Globals::getTableGlobal();
                $sys = SystemUtil::createSystem($table);
                $search = new Search($table);
                $search_tmp = $sys->searchProcPasral($table, $search);
                break;
            default:
                return;
        }
        if(Globals::session("LOGIN_TYPE") == 'admin')
        {
            if($table == 'pay')
            {
                $filename = date('YmdHi').".csv";
                $fields = array("注文番号","アイテムid","アイテム名","アイテムタイプ","アイテムサイズ","　アイテム価格",
                    "名前","住所","メールアドレス","会社名","購入日時","トータル金額","ギフト価格","ギフトピンク",
                    "ギフトブルー","ギフトイエロー","サイドチチ料","サイドチチテキスト","アッパー チップ料",
                    "アッパー チップテキスト","チチカラー料","チチカラーテキスト","変形カット料","変形カットテキスト");

                if($search_tmp['where']) {
                    foreach ($search_tmp['where'] as $val) {
                        if($val['name'] == 'pay_total' && $val['operator'] == 'BETWEEN') {
                            $where = 'WHERE pay_total BETWEEN ' . $val['value'][0] . ' AND ' .  $val['value'][1];
                        }
                    }
                }
            }
        }

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        $f = fopen("php://output", 'w');
        fputcsv($f, $fields);

        $offset = 0;
        while(true){
            $query = 'SELECT *, master_item_type_size.NAME AS item_size 
							FROM
								(
							SELECT
								pay.company,
								pay.`name` as user_name,
								pay.add_sub,
								pay.mail,
								pay.pay_num,
								pay.conf_datetime,
								pay.pay_total,
								pay.gift_price,
								pay.gift_pink,
								pay.gift_blue,
								pay.gift_yellow,
								pay_item.item,
								pay.regist_unix,
								pay_item.item_name,
								pay_item.item_type,
								pay_item.item_type_size,
								pay_item.item_price,
								pay_item.side_chinchi_fee,
								pay_item.side_chinchi_text,
								pay_item.upper_tip_fee,
								pay_item.upper_tip_text,
								pay_item.chichi_color_fee,
								pay_item.chichi_color_text,
								pay_item.deformation_cut_fee,
								pay_item.deformation_cut_text
							FROM
								pay
								LEFT JOIN pay_item ON pay.id = pay_item.pay ';
            if(empty($where)){
                $where = "WHERE is_pasral = 1 AND pay_type != 'reward_market'";
            }else{
                $where = $where." AND is_pasral = 1  AND pay_type != 'reward_market'";
            }
            $query .= $where;
            $query .= ' ) as tbl1 LEFT JOIN master_item_type_size ON tbl1.item_type_size = master_item_type_size.id order by regist_unix DESC limit 1000 offset ' . $offset;

            $offset += 1000;

            $result = $sql->queryRaw($table, $query);
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $lineData = [
                    $rec['pay_num'], $rec['item'], $rec['item_name'], $rec['item_type'],
                    $rec['item_size'], $rec['item_price'], $rec['item_name'], $rec['add_sub'],
                    $rec['mail'], $rec['company'], $rec['conf_datetime'], $rec['pay_total'],
                    $rec['gift_price'], $rec['gift_pink'], $rec['gift_blue'], $rec['gift_yellow'],
                    $rec['side_chinchi_fee'], $rec['side_chinchi_text'], $rec['upper_tip_fee'], $rec['upper_tip_text'],
                    $rec['chichi_color_fee'], $rec['chichi_color_text'], $rec['deformation_cut_fee'], $rec['deformation_cut_text']
                ];

                fputcsv($f, $lineData);
            }
            if($result->num_rows < 1000) {
                break;
            }
        }
        exit;
    }

	static function itemReg()
	{
		if($cart_id = Globals::get("cart_id"))
		{
			$cart = Globals::session("CART_ITEM");
			if(isset($cart[$cart_id]))
			{
				switch($cart[$cart_id]["design_type"])
				{
					case 'new':
					case 'edit':

						//商品同時登録
						if($item_rec = getCartItem(Globals::session("LOGIN_ID"), $cart[$cart_id], true))
						{
							unset($cart[$cart_id]);
							Globals::setSession("CART_ITEM", $cart);
							HttpUtil::location("/info.php?type=item&design=my&id=".$item_rec["id"]);
						}
                    case 'select':
                        HttpUtil::location("/edit.php?type=item&design=my&id=".$cart[$cart_id]['item_id']);
				}
			}
		}
		SystemUtil::errorPage();
	}

	static function addItemFavorite()
	{
		global $sql;

		if(!$item = Globals::get("id")) return;
		if(!$i_rec = $sql->selectRecord("item", $item)) return;
		if(checkItemFavorite(Globals::session("LOGIN_ID"), $item)) return;

		//お気に入りリストに追加
		$table = "item_favorite";
		$tmp_rec = $sql->setData($table, null, "id", SystemUtil::getUniqId($table, false, true));
		$tmp_rec = $sql->setData($table, $tmp_rec, "user", Globals::session("LOGIN_ID"));
		$tmp_rec = $sql->setData($table, $tmp_rec, "item", $item);
		$tmp_rec = $sql->setData($table, $tmp_rec, "regist_unix", time());
		$sql->addRecord($table, $tmp_rec);
	}
	static function changeCartStudent()
	{
		global $sql;

		$is_student = Globals::post("is_student");

		if($is_student){
			Globals::setSession("CART_STUDENT", Globals::post("school_name"));
		}else{
			Globals::setSession("CART_STUDENT", "");
		}

		HttpUtil::location("/page.php?p=cart", true);
	}
	static function delItemFavorite()
	{
		global $sql;

		if(!$item = Globals::get("id")) return;
		if(!$i_rec = $sql->selectRecord("item", $item)) return;
		if(!checkItemFavorite(Globals::session("LOGIN_ID"), $item)) return;

		//お気に入りリストを削除
		$table = "item_favorite";
		$where = $sql->setWhere($table, null, "user", "=", Globals::session("LOGIN_ID"));
		$where = $sql->setWhere($table, $where, "item", "=", $item);

		if(!$uf_rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, array(0, 1)))) return;
		$sql->deleteRecord($table, $uf_rec["id"]);
	}

	static function web2webDirect()
	{
		global $sql;

		if(!$items = Globals::post("data")) SystemUtil::errorPage();

		$itemData = json_decode($items);
        set_cart_type(false);
		$contents= $itemData->contents;
		$direct_mode = $itemData->direct_mode;
		$directory = $itemData->directory;
		foreach ($itemData->data as $kye => $item) {


			if($cart_row = $item->quantity) {} else {
				$cart_row = 1;
			}

			if(!$it_rec = $sql->selectRecord("master_item_type", $item->item_id)) SystemUtil::errorPage();
			if(!$itsu_rec = $sql->selectRecord("master_item_type_sub", $item->color_id)) SystemUtil::errorPage();
			if(!$itsi_rec= $sql->selectRecord("master_item_type_size", $item->size_id)) SystemUtil::errorPage();

			$item_price = $it_rec["item_price"];

			//印刷代加算
			if($item->item_image1) $item_price += $itsu_rec["cost1"];	//表
			if($item->item_image2) $item_price += $itsu_rec["cost2"];	//裏
			if($item->item_image3) $item_price += $itsu_rec["cost3"];	//袖
			if($item->item_image4) $item_price += $itsu_rec["cost3"];	//袖

			$group_id = SystemUtil::getUniqId("cart", false, true);

			$item_type_size_detail = array();
			foreach ($item->item_type_size_detail as $row) {
				if (array_key_exists($row->size_id, $item_type_size_detail)) {
                    $item_type_size_detail[$row->size_id]['total'] += $row->quantity;
                } else {
                    $item_type_size_detail[$row->size_id] = array(
                        'item_type_size' => $row->size_id,
                        'total' => $row->quantity
                    );
                }
            }

			$cart_data = array(
					'design_type' => "new",
					'cart_id' => SystemUtil::getUniqId("cart", false, true),
					'cart_row' => $cart_row,
					'cart_price' => $item_price,
					'item_id' => "",
					'item_type' => $it_rec["id"],
					'item_type_sub' => $itsu_rec["id"],
					'item_type_size' => $itsi_rec["id"],
					'item_price' => $item_price,
					'image_id' => "",
					'image_preview1' => $item->image_preview1,
					'image_preview2' => $item->image_preview2,
					'image_preview3' => $item->image_preview3,
					'image_preview4' => $item->image_preview4,
					'image_path1' => $item->item_image1,
					'image_path2' => $item->item_image2,
					'image_path3' => $item->item_image3,
					'image_path4' => $item->item_image4,
					'option_data' => '',
					'option_price' => 0,
					'design_editable' => 0,
					//
					'direct_mode' => $direct_mode,
					'directory' => $directory,
					'item_name' => $item->item_name,
					'color' => $item->color,
					'design' => $item->design,
					'size' => $item->size,
					'direct_group' => $group_id,
					'design_from' => 'up-t',
					'item_type_size_detail' => $item_type_size_detail,
					//'memo' => $item->contents,
					//'file_name' => $item->file_name,
			);

			//カート追加
			$cart = Globals::session("CART_ITEM");
			foreach ($cart as $tmp_item) {
				if($tmp_item['item_type'] == $it_rec["id"] && $tmp_item['color'] == $item->color && $tmp_item['image_preview1'] == $item->image_preview1 && $tmp_item['image_preview2'] == $item->image_preview2 && $tmp_item['image_preview3'] == $item->image_preview3 && $tmp_item['image_preview4'] == $item->image_preview4) {
					if($tmp_item['item_type'] == $it_rec["id"]) {
						foreach ($tmp_item['item_type_size_detail'] as $size_detail) {
						    // check each element in item data
						    foreach ($item->item_type_size_detail as $row) {
						        // check to exist item in cart or not
						        if (checkSizeExists($item->item_type_size_detail, 'size_id',$size_detail['item_type_size'])) {
                                    if ($row->size_id == $size_detail['item_type_size']) {
                                        $cart[$tmp_item['cart_id']]['item_type_size_detail'][$row->size_id]['total'] += $row->quantity;
                                    }
                                } else {
                                    $cart[$tmp_item['cart_id']]['item_type_size_detail'][$row->size_id] = [
                                        'item_type_size' => $row->size_id,
                                        'total' => $row->quantity
                                    ];
                                }
                            }
						}
					}
					break;
				} else {
					$cart[$cart_data["cart_id"]] = $cart_data;
				}
			}
			if ($cart == null) $cart = array();
			if(count($cart) < 1) {
				$cart[$cart_data["cart_id"]] = $cart_data;
			}
			Globals::setSession("CART_ITEM", $cart);
			Globals::setSession("design_from", 'up-t');
			//カート更新
			refreshCart();
		}

		$pre=date("U");
		$file_data = array();
		for ($i = 1; $i <= 4; $i++) {
			if (is_uploaded_file($_FILES["file".$i]["tmp_name"])) {
				$file_name = $directory."/work/" . $pre."_".$i."_".$_FILES["file".$i]["name"];
				if (move_uploaded_file($_FILES["file".$i]["tmp_name"], $file_name)) {
					array_push($file_data, array($_FILES["file".$i]["name"], $file_name));
				}
			}
		}

		$design_data = array(
				'cart_id' => $group_id,
				"contents" => $contents,
				"direct_mode" => $direct_mode,
				"directory" => $directory,
				"file_data" => $file_data,

		);
		$design = Globals::session("DESIGN_ITEM");
		$design[$design_data["cart_id"]] = $design_data;
		Globals::setSession("DESIGN_ITEM", $design);

		if (!empty(Globals::post('images'))) {
            $designs = Globals::session("DESIGN_IMAGES");
            $design_data = array(
                'cart_id' => $group_id,
                "contents" => $contents,
                "direct_mode" => $direct_mode,
                "directory" => $directory,
                "image_urls" => Globals::post('images'),

            );
            $designs[$design_data["cart_id"]] = $design_data;
            Globals::setSession("DESIGN_IMAGES", $designs);
        }


		HttpUtil::location("/page.php?p=cart", true);
	}

	static function createBlankItem() {
		global $sql;

		if(!$itemId = Globals::get("id")) SystemUtil::errorPage();

		if(!$rec = $sql->selectRecord("master_item_type", $itemId)) SystemUtil::errorPage();

        $itemTypeSub = [];
        $itemTypeSize = [];

        $sub_clume = $sql->setClume('master_item_type_sub',null,"id");
        $sub_where = $sql->setWhere('master_item_type_sub',null,"item_type","=",$itemId);
		$sub_rec = $sql->getSelectResult('master_item_type_sub', $sub_where,null,null,$sub_clume);

        while($sub_result = $sql->sql_fetch_assoc($sub_rec)) {
            $query = "SELECT master_item_type_size.id
                  FROM blank_item_stock
				  INNER JOIN master_item_type_sub ON blank_item_stock.item_sub_code = master_item_type_sub.item_code 
				  AND master_item_type_sub.item_type = '" . $rec['id'] . "' AND master_item_type_sub.id = '" . $sub_result['id'] . "'
				  INNER JOIN master_item_type_size ON blank_item_stock.item_size_code = master_item_type_size.item_code 
				  AND master_item_type_size.item_type = '" . $rec['id'] . "'
				  WHERE blank_item_stock.item_code = '" . $rec['item_code'] . "' AND stock > 0";

            if($itemTypeSize = $sql->sql_fetch_assoc($sql->queryRaw('blank_item_stock', $query))){
                $itemTypeSub = $sub_result;
                break;
            }
        }

		$where = $sql->setWhere('master_item_type_sub', null, "item_type", "=", $itemId);
		$where = $sql->setWhere('master_item_type_sub', $where, "is_main", "=", 1);
		$tmp_result = $sql->getSelectResult('master_item_type_sub', $where);

		while($result = $sql->sql_fetch_assoc($tmp_result))
		{
			$previewImage = $result['thumbnail_url'];
		}

		$tableBlankPrice = 'master_blank_item_price';
		$where = $sql->setWhere($tableBlankPrice, null, "item_type", "=", $itemId);
		$where = $sql->setWhere($tableBlankPrice, $where, "item_type_sub", "=", $itemTypeSub['id']);
		$where = $sql->setWhere($tableBlankPrice, $where, "item_type_size", "=", $itemTypeSize['id']);

		$blankItemPrice = $sql->sql_fetch_assoc($sql->getSelectResult($tableBlankPrice, $where));

		$price = $blankItemPrice['price'];

		$contents = [
			'blank_item' => true,
			'design_type' => 'select',
			'item_type' => $itemId,
			'session_wish_list' => Globals::get("session_wish_list"),
			'item_type_sub' => $itemTypeSub['id'],
			'item_type_size' => $itemTypeSize['id'],
			'item_id' => $itemId,
			'blank_item_price' => $price,
			'cart_row' => 1,
			'image_id' => '',
			'image_pre1' => $previewImage,
			'image_pre2' => '',
			'image_pre3' => '',
			'image_pre4' => '',
			'image_path1' => $previewImage,
			'image_path2' => '',
			'image_path3' => '',
			'image_path4' => '',
//			'option_id' => rtrim($option_id, ","),
//			'option_price' => rtrim($option_price, ","),
//			'option_owner' => rtrim($option_owner, ","),
		];

		$itemId = addBlankItem($contents, $rec);

		if($itemId) {
			$contents['item_id'] = $itemId;
		}

		$admin = '';
        if (Globals::session('ADMIN')) {
            $admin = '&admin=true';
        }

        HttpUtil::postLocation("/proc.php?run=appli2web{$admin}", $contents);
	}

	static function web2web()
	{
		global $sql;
        $card_thank = 0;
		if(!$item = Globals::get("id")) SystemUtil::errorPage();
		if(Globals::get("type") == 'blank_prod') {
			$rec = $sql->selectRecord("master_item_type", $item);
		} else {
			$rec = $sql->selectRecord("item", $item);
			if(!empty(Globals::get("run")) && Globals::get("run") == 'web2web'){
                $card_thank = 1;
            }
		}
		if(!$rec) SystemUtil::errorPage();
		if(empty(Globals::session("orderBaseCart"))){
            if($rec["buy_count_state"] && $rec["buy_count_row"] <= 0) SystemUtil::errorPage();	//限定販売数
        }
        if(($rec["buy_state"] == 0 && $rec["user"] != Globals::session("LOGIN_ID")) || $rec["state"] == 0){
            SystemUtil::errorNoBuyPage();
        }

        if($rec['product_type']== RINGPASRAL){
            self::appliPasralAgain();
            HttpUtil::location("/page.php?p=cart");
        }
        else{
            set_cart_type(false);
            //オプションパラメータ
            $option_id = "";
            $option_price = "";
            $option_owner = "";

            $tmp_table = "item_option";
            $tmp_where = $sql->setWhere($tmp_table, null, "item", "=", $rec["id"]);
            $tmp_order = $sql->setOrder($tmp_table, null, "regist_unix", "ASC");
            $tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);
            while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
            {
                $option_id = $tmp_rec["option_id"].",";
                $option_price = "0,";
                $option_owner = $tmp_rec["owner"].",";
            }

            if($cart_row = Globals::get("quantity")) {} else {
                $cart_row = 1;
            }

            //appli2web
            $contents = array(
                'design_type' => 'select',
                'item_type' => $rec["item_type"],
                'session_wish_list' => Globals::get("session_wish_list"),
                'item_type_sub' => $rec["item_type_sub"],
                'item_type_size' => Globals::request("size"),
                'item_id' => $rec["id"],
                'cart_row' => $cart_row,
                'image_id' => $rec["image_id"],
                'image_pre1' => $rec["item_preview1"],
                'image_pre2' => $rec["item_preview2"],
                'image_pre3' => $rec["item_preview3"],
                'image_pre4' => $rec["item_preview4"],
                'image_path1' => $rec["item_image1"],
                'image_path2' => $rec["item_image2"],
                'image_path3' => $rec["item_image3"],
                'image_path4' => $rec["item_image4"],
                'option_id' => rtrim($option_id, ","),
                'option_price' => rtrim($option_price, ","),
                'option_owner' => rtrim($option_owner, ","),
                'card_thank' => $card_thank
            );
            if(Globals::get("size")){
                $contents["item_type_size"]=Globals::get("size");
            }
            if(Globals::get("color")){
                $contents["item_type_sub"]=Globals::get("color");
            }

            $admin = '';
            if (Globals::session('ADMIN')) {
                $admin = '&admin=true';
            }

            if($contents['item_type']== pasral){

            }
            else{
                if (Globals::post('multiple')) {
                    $contents['multiple'] = true;
                    foreach ($contents as $key => $value) {
                        Globals::setPost($key, $value);
                    }

                    self::appli2web();
                } else {
                    HttpUtil::postLocation("/proc.php?run=appli2web{$admin}", $contents);
                }
            }
        }
//
	}

	static function web2appli()
	{
		global $sql;

		if(Globals::session("LOGIN_TYPE") == "user")
			$login = "&login_id=".Globals::session("LOGIN_ID");
		else
			$login = "";

		switch(Globals::get("design_type"))
		{
			case 'new':

				HttpUtil::location(Extension::getDrawToolLinkString());

			case 'select':

				if(!$item = Globals::get("id")) SystemUtil::errorPage();
				if(!$rec = $sql->selectRecord("item", $item)) SystemUtil::errorPage();
				if($rec["buy_count_state"] && $rec["buy_count_row"] <= 0) SystemUtil::errorPage();	//限定販売数

				HttpUtil::location("http://tool.up-t.jp/design.html?design_type=select&item_id=".$rec["id"]."&image_id=".$rec["image_id"].$login);

			case 'edit':

				if(!$item = Globals::get("id")) SystemUtil::errorPage();
				if(!$rec = $sql->selectRecord("item", $item)) SystemUtil::errorPage();

				if($rec["owner_item"]) SystemUtil::errorPage();	//三次利用不可
				if(!$rec["2nd_state"]) SystemUtil::errorPage();	//二次創作不可

				HttpUtil::location("http://tool.up-t.jp/design.html?design_type=edit&item_id=".$rec["id"]."&image_id=".$rec["image_id"].$login);

			default:
				SystemUtil::errorPage();
		}
	}

	static function changeMultiItemState()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
		$count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeItemState($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

    static function changeMultiItemMarketState()
    {
        global $sql;
        $data          = array();
        $data["state"] = -1;
        $ids = [];

        if (!$list = Globals::get("list")) jsonEncode($data);
        $state = Globals::get("state");
        if (!is_numeric($state)) jsonEncode($data);

        $list       = explode("/", $list);
        $count_list = count($list);
        for ($i = 0; $i < $count_list; $i++) {
            $ids[] = $sql->escape($list[$i]);
        }

        $table = 'item';
        $data = $sql->setData($table, null, 'is_wiki', $state);
        $where = $sql->setWhere($table, null, 'id', 'IN', $ids);
        $sql->updateRecordWhere($table, $data, $where);

        $data["state"] = (int)$state;
        jsonEncode($data);
    }

    static function hiddenAllItemOfSelectedUser()
    {
        global $sql;

        $table = 'item';
        $data = $sql->setData($table, null, 'is_wiki', 1);
        $where = $sql->setWhere($table, null, 'user', '=', Globals::get('user_id'));
        $sql->updateRecordWhere($table, $data, $where);

        jsonEncode(['state' => 1]);
    }

	static function changeMultiItemCartState()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeItemCartState($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeMultiUserFee()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeUserFee($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeUserFee()
	{
		$data = array();
		$data["state"] = -1;

		if(!$id = Globals::get("id")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		changeUserFee($id, $state);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changePayPay()
	{
        global $sql;
		$data = array();
		$data["state"] = -1;
        $table = 'pay';

		if(!$id = Globals::get("id")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

        $rec = $sql->selectRecord($table, $id);
        if ($rec['pay_type'] == 'rakuten') {
            //update pay_state cancel
            $update1 = $sql->setData($table, null, "pay_state", $state);
            $sql->updateRecord($table, $update1, $id);
        }
		if(!changePayPay($id, $state)) jsonEncode($data);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changePayPayAfter()
	{
		$data = array();
		$data["state"] = -1;

		if(!$id = Globals::get("id")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		if(!changePayPay($id, $state)) jsonEncode($data);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}



	static function exportToPrintty()
	{
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 0);

		$data = array();

		if(!$ids = array_values(Globals::post("ids"))) jsonEncode($data);
		$state = Globals::post("state");
		if(!is_numeric($state)) jsonEncode($data);

		if(!exportToPrintty($ids, $state, Globals::post('is_kanazawa'))) jsonEncode($data);

		jsonEncode($data);
	}

	static function changeMultiPayDelivery()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			$check = checkPayTypeAfter($list[$i], $state);

			switch ($check)
			{
				case '2':
					$data["state"] = -1;
					jsonEncode($data);
					exit();
					break;
				case '3':
					$data["state"] = 3;
					jsonEncode($data);
					exit();
					break;
				case '4':
					$data["state"] = 4;
					jsonEncode($data);
					exit();
					break;
				case '5':
					$data["state"] = 5;
					jsonEncode($data);
					exit();
					break;
				case '6':
					$data["state"] = 3;
					jsonEncode($data);
					exit();
					break;
				default :
					break;
			}

			changePayDelivery($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeMultiPirnt()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changePirnt($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeMultiGarment()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeGarment($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function printReceipt()
	{
		$data = array();
        $type = Globals::get("type");

		if(!$list = Globals::get("list")) jsonEncode($data);

		$date = printReceipts($list, $type);
	}

    static function printReceiptPasral()
    {
        $data = array();
        $type = Globals::getTableGlobal();

        if(!$list = Globals::get("list")) jsonEncode($data);

        $date = printReceipts($list, $type);
    }

	static function printPreviewReceipt()
	{
		global $sql;
		$sys = SystemUtil::createSystem();
		if($payId = Globals::get("id")) {
			$rec = $sql->selectRecord(Globals::get("type"), $payId);
		} else {
			$rec = $sys->setRequestData(Globals::get("type"), "regist");
		}

		printPreviewReceipts($rec);
	}

	static function downloadReceipt() {
		global $sql;

		if($payId = Globals::get("id")) {
			$rec = $sql->selectRecord(Globals::get("type"), $payId);
			if(Globals::get('preview') == 'true') {
				downloadReceiptPdf($rec, 'preview');
				return;
			}
			$content = downloadReceiptPdf($rec);

			header("Content-Type: application/pdf");
			header("Content-Disposition:attachment;filename='receipt-". $rec['pay_num'] .".pdf'");
			$path = fopen('attachments/receipt-'. $rec['pay_num'] .'.pdf', 'w');
			fputs($path, $content);
			readfile('attachments/receipt-' . $rec['pay_num'] . '.pdf');
			unlink('attachments/receipt-' . $rec['pay_num'] . '.pdf');
			fclose($path);
		}
	}

	static function changePayDelivery()
	{
		$data = array();
		$data["state"] = -1;

		if(!$id = Globals::get("id")) jsonEncode($data);
		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$check = checkPayTypeAfter($id, $state);

		switch ($check)
		{
			case '2':
				$data["state"] = -1;
				jsonEncode($data);
				exit();
				break;
			case '3':
				$data["state"] = 3;
				jsonEncode($data);
				exit();
				break;
			case '4':
				$data["state"] = 4;
				jsonEncode($data);
				exit();
				break;
			case '5':
				$data["state"] = 5;
				jsonEncode($data);
				exit();
				break;
			case '6':
				$data["state"] = 3;
				jsonEncode($data);
				exit();
				break;
			default :
				break;
		}
        $delivery_service = null;
        if (!empty(Globals::get("delivery_service")) && Globals::get("delivery_service") != 'undefined') {
            $delivery_service = Globals::get("delivery_service");
        }

        $tracking_number = null;
        if (!empty(Globals::get("tracking_number")) && Globals::get("tracking_number") != 'undefined') {
            $tracking_number = Globals::get("tracking_number");
        }

		changePayDelivery($id, $state,false,null,$tracking_number,$delivery_service);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeMultiMasterState()
	{
        global $sql;

		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);
		if(!$table = Globals::get("type")) jsonEncode($data);

		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

        $list = explode("/", $list);

		if ($state == 0) {
            foreach ($list as $productId) {
                $product = $sql->selectRecord($table, $productId);
                if ($product != NULL && $product['is_main'] == 1) {
                    $data["state"] = 9;
                    jsonEncode($data);
                    return;
                }
            }
        }

        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeMasterState($table, $list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeMasterState()
	{
        global $sql;

		$data = array();
		$data["state"] = -1;

		if(!$id = Globals::get("id")) jsonEncode($data);
		if(!$table = Globals::get("type")) jsonEncode($data);

		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

        if ($state == 0) {
            $item = $sql->selectRecord($table, $id);
            if ($item != NULL && $item['is_main'] == 1) {
                $data["state"] = 9;
                jsonEncode($data);
                return;
            }
        }

		changeMasterState($table, $id, $state);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

    static function changeCategoryState()
    {
        global $sql;

        $data = array();
        $data["state"] = -1;

        if(!$id = Globals::get("id")) jsonEncode($data);
        if(!$table = Globals::get("type")) jsonEncode($data);

        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);
        changeCategoryState($table, $id, $state);

        $data["state"] = $state + 0;
        jsonEncode($data);
    }

	static function changeMultiOwnerState()
	{
		$data = array();
		$data["state"] = -1;

		if(!$list = Globals::get("list")) jsonEncode($data);

		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		$list = explode("/", $list);
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			changeOwnerState($list[$i], $state);
		}

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeOwnerState()
	{
		$data = array();
		$data["state"] = -1;

		if(!$id = Globals::get("id")) jsonEncode($data);

		$state = Globals::get("state");
		if(!is_numeric($state)) jsonEncode($data);

		changeOwnerState($id, $state);

		$data["state"] = $state + 0;
		jsonEncode($data);
	}

	static function changeSpecification(){
		if(!empty(Globals::post("cart_id"))) {
			$cart_id = Globals::post("cart_id");
			$cart = Globals::session("CART_ITEM");
			if(isset($cart[$cart_id]))
			{

				if(Globals::post("side_chinchi") == 1){
					$cart[$cart_id]["side_chinchi"]['fee'] = 0;
					$cart[$cart_id]["side_chinchi"]['text'] = '側面チチ(右)';
				}else if(Globals::post("side_chinchi") == 2){
					$cart[$cart_id]["side_chinchi"]['fee'] = 0;
					$cart[$cart_id]["side_chinchi"]['text'] = '側面チチ(無し)';
				}else{
					$cart[$cart_id]["side_chinchi"]['fee'] = 0;
					$cart[$cart_id]["side_chinchi"]['text'] = '側面チチ(左)';
				}

				if(Globals::post("upper_tip") == 1){
					$cart[$cart_id]["upper_tip"]['fee'] = 0;
					$cart[$cart_id]["upper_tip"]['text'] = '上部チチ(無し)';
				}else{
					$cart[$cart_id]["upper_tip"]['fee'] = 0;
					$cart[$cart_id]["upper_tip"]['text'] = '上部チチ(上部)';
				}

				if(Globals::post("chichi_color") == 1){
					$cart[$cart_id]["chichi_color"]['fee'] = 300;
					$cart[$cart_id]["chichi_color"]['text'] = 'チチ色(黒)';
				}else{
					$cart[$cart_id]["chichi_color"]['fee'] = 0;
					$cart[$cart_id]["chichi_color"]['text'] = 'チチ色(白)';
				}

				if(Globals::post("deformation_cut") == 1){
					$cart[$cart_id]["deformation_cut"]['fee'] = 100;
					$cart[$cart_id]["deformation_cut"]['text'] = '変形カット(Rカット)';
				}else if(Globals::post("deformation_cut") == 2){
					$cart[$cart_id]["deformation_cut"]['fee'] = 100;
					$cart[$cart_id]["deformation_cut"]['text'] = '変形カット(Vカット)';
				}else if(Globals::post("deformation_cut") == 3){
					$cart[$cart_id]["deformation_cut"]['fee'] = 100;
					$cart[$cart_id]["deformation_cut"]['text'] = '変形カット(Aカット)';
				}else{
					$cart[$cart_id]["deformation_cut"]['fee'] = 0;
					$cart[$cart_id]["deformation_cut"]['text'] = '変形カット(通常カット)';
				}

			}

			Globals::setSession("CART_ITEM", $cart);
		}
		//カート更新
		refreshCart();

		HttpUtil::location("/page.php?p=cart", true);

	}

	static function changeCart()
	{
		global $sql;

		$cart = Globals::session("CART_ITEM");
		$cart_id = Globals::post("cart_id");
		$isChangeSize = false;
		$reload = '';

		if(Globals::post('size')) {
			if(Globals::post('size') != $cart[$cart_id]['item_type_size']) $isChangeSize = true;
		}
        if(isset($cart_id) && !empty($cart[$cart_id]['product_type']) && $cart[$cart_id]['product_type'] == RINGPASRAL){
            self::changeCartPasral();
        }
		if(isset($cart_id))
		{
			//数量変更
			unset($cart[$cart_id]['item_type_size_detail']);
			$item_type = $cart[$cart_id]['item_type'];
			$itemTypeSizes = $sql->queryRaw('master_item_type_size', "select id, name from master_item_type_size where item_type like '$item_type'");
			foreach ($itemTypeSizes as $itemTypeSize) {
			    $cart_key = $cart_id;
				$cart_row = Globals::post("cart_row_".$cart_id."_".$itemTypeSize['id']);
				if(!empty($cart_row) && is_numeric($cart_row) && $cart_row > 0)
				{
					$item_type_size = $itemTypeSize['id'];

					$plain_price = plain_price($cart, $cart_id, $item_type_size);

					if ($plain_price['price'] > 0) {
                        $reload = '&reload=1';

                        if ($plain_price['id'] == $cart_id) {
                            Globals::setSession("CART_ITEM", $cart);

                            appliMultipleItemBlank(['fee_owner' => 0, 'fee_user' => 0], $item_type,
                                $cart[$cart_id]['item_type_sub'], $item_type_size, $plain_price['price'], $cart_row,
                                $cart[$cart_id]['image_preview1'], []);
                            $cart = Globals::session("CART_ITEM");

                            continue;
                        } else {
                            $cart_key = $plain_price['id'];
                        }
                    }

					$cart[$cart_key]["item_type_size_detail"][$item_type_size]['item_type_size'] = $item_type_size;
                    $cart[$cart_key]["item_type_size_detail"][$item_type_size]['total'] = $cart_row;
                    $cart[$cart_key]['item_type_size'] = $item_type_size;
                    $cart[$cart_key]['size'] = $itemTypeSize['name'];
                    if($cart[$cart_key]['product_type'] == 'bl') {
                    	$cart[$cart_key]["item_type_size_detail"][$item_type_size]['product_type'] = 'blank';
					}
                }
			}

			//cal total item of nobori
			$cart_row_nobori = Globals::post("cart_row_" . $cart_id . "_" . "ITSI4309");
			$count = 0;
			foreach ($cart as $val) {
				if($val['item_type'] == 'IT309') {
					$count++;
				}
			}
			if($count < 2 && $cart[$cart_id]["item_type_size_detail"]['total'] < 2 && $cart[$cart_id]["item_type"] == 'IT309' && $cart_row_nobori < 2) {
				$cart[$cart_id]['item_type_size_detail']['ITSI4309']['item_type_size'] = 'ITSI4309';
				$cart[$cart_id]['item_type_size_detail']['ITSI4309']['total'] = 2;
			} else if(is_numeric($cart_row_nobori) && $cart_row_nobori > 0){
				$cart[$cart_id]['item_type_size_detail']['ITSI4309']['item_type_size'] = 'ITSI4309';
				$cart[$cart_id]['item_type_size_detail']['ITSI4309']['total'] = $cart_row_nobori;
			}

			//サイズ変更
			if($item_type_size)
			{
				if($itsi_rec = $sql->selectRecord("master_item_type_size", $item_type_size))
				{
					if($itsi_rec["item_type"] == $cart[$cart_id]["item_type"])
					{
						$cart[$cart_id]["item_type_size"] = $itsi_rec["id"];
					}
				}
			}

			//色変更
			if($item_type_sub = Globals::post("item_type_sub"))
			{
				$itsub_rec_org = $sql->selectRecord("master_item_type_sub", $cart[$cart_id]["item_type_sub"]);
				if($itsub_rec = $sql->selectRecord("master_item_type_sub", $item_type_sub))
				{
					if($itsub_rec["item_type"] == $cart[$cart_id]["item_type"])
					{
						if (Globals::post("product_id")) {
							$cart[$cart_id]["item_id"] = Globals::post("product_id");
							$cart[$cart_id]["item_type_sub"] = $itsub_rec["id"];

							$item_table = "item";
                            $item_update = $sql->setData($item_table, null, "item_type_sub", $item_type_sub);

                            $product_images = json_decode(Globals::post("product_images"));
                            foreach ($product_images as $key => $value) {
                                $cart[$cart_id]["image_preview".$value->ProductImage->side] = $value->ProductImage->image_url;
                                $item_update = $sql->setData($item_table, $item_update, "item_preview".$value->ProductImage->side, $value->ProductImage->image_url);
                            }

                            $sql->updateRecord($item_table, $item_update, Globals::post("product_id"));
						} else {
							$product_images = json_decode(Globals::post("product_images"));
							$cart[$cart_id]["image_id"] = Globals::post("image_id");
							$cart[$cart_id]["item_type_sub"] = $itsub_rec["id"];
							$cart[$cart_id]["image_preview1"] = $product_images->image_pre1;
							$cart[$cart_id]["image_preview2"] = $product_images->image_pre2;
							$cart[$cart_id]["image_preview3"] = $product_images->image_pre3;
							$cart[$cart_id]["image_preview4"] = $product_images->image_pre4;
							$cart[$cart_id]["image_path1"] = $product_images->image_path1;
							$cart[$cart_id]["image_path2"] = $product_images->image_path2;
							$cart[$cart_id]["image_path3"] = $product_images->image_path3;
							$cart[$cart_id]["image_path4"] = $product_images->image_path4;
						}
					}

					$price_diff = 0;
					$embroidery_print = json_decode($cart[$cart_id]['embroidery_print'], true);
                    for ($i = 1; $i <= 4; $i++) {
                        if ((empty($embroidery_print) || $embroidery_print['print'][$i]) && $cart[$cart_id][sprintf('image_path%s', $i)]) {
                            $side = $i;

                            if ($i == 4) {
                                $side = 3;
                            }

                            $side_name = sprintf('cost%s', $side);
                            $price_diff += $itsub_rec[$side_name] - $itsub_rec_org[$side_name];
                        }
                    }

					if($cart[$cart_id]['product_type'] == 'bl') {
						$tableBlankPrice = 'master_blank_item_price';
						$where = $sql->setWhere($tableBlankPrice, null, "item_type", "=", $cart[$cart_id]['item_type']);
						$where = $sql->setWhere($tableBlankPrice, $where, "item_type_sub", "=", Globals::post('item_type_sub'));
						$where = $sql->setWhere($tableBlankPrice, $where, "item_type_size", "=", $cart[$cart_id]['item_type_size']);

						$blankItemPrice = $sql->sql_fetch_assoc($sql->getSelectResult($tableBlankPrice, $where));
						$price_diff = $blankItemPrice['price'] - $cart[$cart_id]["item_price"];
					}
					$cart[$cart_id]["item_price"] = $cart[$cart_id]["item_price"] + $price_diff;
					$cart[$cart_id]["cart_price"] = $cart[$cart_id]["cart_price"] + $price_diff;
				}
			}
			if($isChangeSize && $cart[$cart_id]['product_type'] == 'bl') {
				$tableBlankPrice = 'master_blank_item_price';
				$where = $sql->setWhere($tableBlankPrice, null, "item_type", "=", $cart[$cart_id]['item_type']);
				$where = $sql->setWhere($tableBlankPrice, $where, "item_type_sub", "=", $cart[$cart_id]['item_type_sub']);
				$where = $sql->setWhere($tableBlankPrice, $where, "item_type_size", "=", Globals::post('size'));

				$blankItemPrice = $sql->sql_fetch_assoc($sql->getSelectResult($tableBlankPrice, $where));
				if($blankItemPrice) {
					$price_diff = $blankItemPrice['price'] - $cart[$cart_id]["item_price"];
					$cart[$cart_id]["item_price"] = $cart[$cart_id]["item_price"] + $price_diff;
					$cart[$cart_id]["cart_price"] = $cart[$cart_id]["cart_price"] + $price_diff;
				}
			}

			// Update print by layers activation flag
			if (Globals::post("print_by_layers_activated") != NULL) {
				$cart[$cart_id]["print_by_layers_activated"] = Globals::post("print_by_layers_activated");

				//Update price of print by layer
				if ($cart[$cart_id]["print_by_layers_activated"] == 1) {
					if ($cart_item_id = self::getNextPriceItemCart($cart, $cart_id)) {
						$plate_price = $cart[$cart_item_id]['print_by_layers_data']['price_details']['base_price_for_production'];
						$cart[$cart_id]['print_by_layers_data']['price_details']['base_price_for_production'] = 0;
						$cart[$cart_id]['print_price_tmp'] = $plate_price;
					} else {
						$cart_target_id = !empty($cart[$cart_id]['copy_from']) ? $cart[$cart_id]['copy_from'] : $cart_id;
						$plate_price = $cart[$cart_target_id]['print_by_layers_data']['price_details']['base_price_for_production'];
						$cart[$cart_id]['print_by_layers_data']['price_details']['base_price_for_production'] = $plate_price;
						$cart = self::setAllCloneCartItemBase($cart, $cart_id);
					}
				} elseif ($cart[$cart_id]["print_by_layers_activated"] == 0) {
					if(($cart_item_id = self::getPrintLayerCartItem($cart, $cart_id, false)) && !self::getNextPriceItemCart($cart, $cart_id)) {
						$cart_target_id = empty($cart[$cart_id]['copy_from']) ? $cart_id : $cart[$cart_id]['copy_from'];
						$plate_price = $cart[$cart_target_id]['print_by_layers_data']['price_details']['base_price_for_production'];
						$cart[$cart_item_id]['print_by_layers_data']['price_details']['base_price_for_production'] = $plate_price;
						$cart = self::setAllCloneCartItemBase($cart, $cart_item_id);
					}
				}
			}

			//デザイン変更
			if (Globals::post("design_type") == 'front_design_type') {
				$cart[$cart_id]["front_design_type"] = Globals::post("front_design_type");
			}
			if (Globals::post("design_type") == 'back_design_type') {
				$cart[$cart_id]["back_design_type"] = Globals::post("back_design_type");
			}
			if (Globals::post("design_type") == 'left_design_type') {
				$cart[$cart_id]["left_design_type"] = Globals::post("left_design_type");
			}
			if (Globals::post("design_type") == 'right_design_type') {
				$cart[$cart_id]["right_design_type"] = Globals::post("right_design_type");
			}
		}

		Globals::setSession("CART_ITEM", $cart);

		//カート更新
		refreshCart();

		HttpUtil::location("/page.php?p=cart" . $reload, true);
	}

	// change cart_row and price
    static function changeCartPasral()
    {
        global $sql;

        $cart = Globals::session("CART_ITEM");
        $cart_id = Globals::post("cart_id");

        if(isset($cart_id))
        {
            //数量変更
            $cart_row = Globals::post("cart_row_".$cart_id);
            if(!empty($cart_row) && is_numeric($cart_row) && $cart_row > 0){
                if(!empty($cart_row) && is_numeric($cart_row) && $cart_row > 0){
                    $cart[$cart_id]['item_type_size_detail'][0]['total'] = $cart_row;
                    $cart[$cart_id]['cart_row'] = $cart_row;
                    $cart[$cart_id]['cart_price'] = $cart[$cart_id]['item_price'];
                }
            }else{
                unset($cart[$cart_id]);
            }
        }

        Globals::setSession("CART_ITEM", $cart);

        //カート更新
        refreshCart();

        HttpUtil::location("/page.php?p=cart", true);
    }

	static function delSizeCart(){
		$cart = Globals::session("CART_ITEM");
		$cartId = Globals::get("cart_id");
		$itemTypesize = Globals::get("item_type_size");

		unset($cart[$cartId]['item_type_size_detail'][$itemTypesize]);

		Globals::setSession("CART_ITEM", $cart);

		//カート更新
		refreshCart();

		HttpUtil::location("/page.php?p=cart", true);
	}

	static function addSizeCart() {
		global $sql;

		$cart = Globals::session("CART_ITEM");
		$cartId = Globals::get("cart_id");
		$itemType = $cart[$cartId]['item_type'];
		$itemTypeSub = $cart[$cartId]['item_type_sub'];
		$listSizeDisable = Process::listSize($itemType, $itemTypeSub);
		$itemTypeSizes = $sql->queryRaw('master_item_type_size', "select id from master_item_type_size where item_type like '$itemType'");
		foreach ($itemTypeSizes as $ItemTypeSize) {
			if(!array_key_exists($ItemTypeSize['id'], $cart[$cartId]['item_type_size_detail']) && !in_array($ItemTypeSize['id'], $listSizeDisable)) {

				$cart[$cartId]['item_type_size_detail'][$ItemTypeSize['id']]['item_type_size'] = $ItemTypeSize['id'];
				$cart[$cartId]['item_type_size_detail'][$ItemTypeSize['id']]['total'] = 1;
				break;
			}
		}
		Globals::setSession("CART_ITEM", $cart);

		//カート更新
		refreshCart();

		HttpUtil::location("/page.php?p=cart", true);
	}

	static function listSize($itemType, $itemTypeSub) {
		global $sql;

		$is_table = "item_stock";
		$mit_size_table = "master_item_type_size";

		$mit_rec = $sql->selectRecord("master_item_type", $itemType);
		$mit_sub_rec = $sql->selectRecord("master_item_type_sub", $itemTypeSub);

		$where = $sql->setWhere($is_table, null, "item", "=", $mit_rec["item_code"]);
		$where = $sql->setWhere($is_table, $where, "item_type_sub_code", "=", $mit_sub_rec["item_code"]);

		$tmp_array = array();
		$result = $sql->getSelectResult($is_table, $where);
		while ($rec = $sql->sql_fetch_assoc($result)) {
			$where2 = $sql->setWhere($mit_size_table, null, "item_type", "=", $itemType);
			$where2 = $sql->setWhere($mit_size_table, $where2, "item_code", "=", $rec["item_type_size_code"]);
			$rec2 = $sql->sql_fetch_assoc($sql->getSelectResult($mit_size_table, $where2, null, array(0, 1)));

			$tmp_array[] = $rec2["id"];
		}

		return $tmp_array;
	}

	/**
	 * Return next item that has print price
	 * @param $cart
	 * @param $cart_id
	 * @return int|null|string
	 */
	static function getNextPriceItemCart($cart, $cart_id)
	{
		$cart_target_id = !empty($cart[$cart_id]['copy_from']) ? $cart[$cart_id]['copy_from'] : $cart_id;
		foreach ($cart as $id => $cart_item) {
			if($id == $cart_id)
				continue;
			$cart_base_id = !empty($cart_item['copy_from']) ? $cart_item['copy_from'] : $id;
			if ($cart_base_id == $cart_target_id && $cart_item['print_by_layers_data']['price_details']['base_price_for_production'] > 0 && $cart_item["print_by_layers_activated"] == 1)
				return $id;
		}
		return null;
	}

	static function changeCartGift()
	{
		global $sql;

		$gift_array = array(
			"pink",
			"blue",
			"yellow"
			);
		if($gift_color = Globals::post("color"))
		{
			if(in_array($gift_color, $gift_array))
			{
				if(!$gift = Globals::session("CART_GIFT"))
				{
					$gift = array(
						"pink" => array("gift_row" => 0),
						"blue" => array("gift_row" => 0),
						"yellow" => array("gift_row" => 0),
					);
				}
				$gift_row = Globals::post("gift_row");
				if(is_numeric($gift_row))
				{
					$gift[$gift_color]["gift_row"] = $gift_row;
				}

				Globals::setSession("CART_GIFT", $gift);
			}
		}

		HttpUtil::location("/page.php?p=cart", true);
	}

	static function changePayGift()
	{
		global $sql;

		$table = "pay";
		$gift_row = Globals::post("gift_row");
		if($rec = $sql->selectRecord($table, Globals::post("id")))
		{
			if($gift_color = Globals::post("color"))
			{
				if(is_numeric($gift_row))
				{
					switch ($gift_color)
					{
						case 'pink':
							$update = $sql->setData($table, null, "gift_".$gift_color, $gift_row);
							$gift_total = ($update["gift_".$gift_color] + $rec["gift_blue"] + $rec["gift_yellow"]) * $rec["gift_price"];
							$total = $gift_total + $rec["pay_price"] - $rec["pay_discount"] - $rec["pay_promotion"];
							$total_without_promotion = $total + $rec["pay_promotion"];
							$tax_tmp = ceil($total * getTaxRate($rec["regist_unix"]));
							$total = $total + $tax_tmp;
							$update = $sql->setData($table, $update, "pay_postage", getPostage($rec["add_pre"], $total_without_promotion, $rec["pay_type"]));
							$pay_total_tmp = $rec["pay_price"] + $update["pay_postage"] + $rec["pay_cod"] + $rec["pay_adjustment"] - $rec["pay_discount"] - $rec["pay_promotion"] + $gift_total - $rec["pay_point"] + $rec["deferred_payment"] - $rec["pay_rank"];
							$pay_tax = ceil($pay_total_tmp * getTaxRate($rec["regist_unix"]));
							$update = $sql->setData($table, $update, "pay_tax", $pay_tax);
							$update = $sql->setData($table, $update, "pay_total", $pay_total_tmp + $update["pay_tax"]);
							$sql->updateRecord($table, $update, $rec["id"]);
							break;
						case 'blue':
							$update = $sql->setData($table, null, "gift_".$gift_color, $gift_row);
							$gift_total = ($rec["gift_pink"] + $update["gift_".$gift_color] + $rec["gift_yellow"]) * $rec["gift_price"];
							$total = $gift_total + $rec["pay_price"] - $rec["pay_discount"] - $rec["pay_promotion"];
							$total_without_promotion = $total + $rec["pay_promotion"];
							$tax_tmp = ceil($total * getTaxRate($rec["regist_unix"]));
							$total = $total + $tax_tmp;
							$update = $sql->setData($table, $update, "pay_postage", getPostage($rec["add_pre"], $total_without_promotion, $rec["pay_type"]));
							$pay_total_tmp = $rec["pay_price"] + $update["pay_postage"] + $rec["pay_cod"] + $rec["pay_adjustment"] - $rec["pay_discount"] - $rec["pay_promotion"] + $gift_total - $rec["pay_point"] + $rec["deferred_payment"] - $rec["pay_rank"];
							$pay_tax = ceil($pay_total_tmp * getTaxRate($rec["regist_unix"]));
							$update = $sql->setData($table, $update, "pay_tax", $pay_tax);
							$update = $sql->setData($table, $update, "pay_total", $pay_total_tmp + $update["pay_tax"]);
							$sql->updateRecord($table, $update, $rec["id"]);
							break;
						case 'yellow':
							$update = $sql->setData($table, null, "gift_".$gift_color, $gift_row);
							$gift_total = ($rec["gift_pink"] + $rec["gift_blue"] + $update["gift_".$gift_color]) * $rec["gift_price"];
							$total = $gift_total + $rec["pay_price"] - $rec["pay_discount"] - $rec["pay_promotion"];
							$total_without_promotion = $total + $rec["pay_promotion"];
							$tax_tmp = ceil($total * getTaxRate($rec["regist_unix"]));
							$total = $total + $tax_tmp;
							$update = $sql->setData($table, $update, "pay_postage", getPostage($rec["add_pre"], $total_without_promotion, $rec["pay_type"]));
							$pay_total_tmp = $rec["pay_price"] + $update["pay_postage"] + $rec["pay_cod"] + $rec["pay_adjustment"] - $rec["pay_discount"] - $rec["pay_promotion"] + $gift_total - $rec["pay_point"] + $rec["deferred_payment"] - $rec["pay_rank"];
							$pay_tax = ceil($pay_total_tmp * getTaxRate($rec["regist_unix"]));
							$update = $sql->setData($table, $update, "pay_tax", $pay_tax);
							$update = $sql->setData($table, $update, "pay_total", $pay_total_tmp + $update["pay_tax"]);
							$sql->updateRecord($table, $update, $rec["id"]);
							break;
						default :
							break;
					}
				}
			}

            HttpUtil::location($_SERVER['HTTP_REFERER']);
		}
	}

	static function changePayAdjustment()
	{
		global $sql;

		$table = "pay";
		if($rec = $sql->selectRecord($table, Globals::post("id")))
		{
			$pay_adjustment = Globals::post("pay_adjustment");

			if(is_numeric($pay_adjustment))
			{
				$gift_total = ($rec["gift_pink"] + $rec["gift_blue"] + $rec["gift_yellow"]) * $rec["gift_price"];
				$pay_total_tmp = $rec["pay_price"] + $rec["pay_postage"] + $rec["pay_cod"] + $pay_adjustment - $rec["pay_discount"] - $rec["pay_promotion"] + $gift_total - $rec["pay_point"]+$rec["deferred_payment"] - $rec["pay_rank"];
				$pay_tax = ceil($pay_total_tmp * getTaxRate($rec["regist_unix"]));
				$update = $sql->setData($table, null, "pay_tax", $pay_tax);
				$update = $sql->setData($table, $update, "pay_total", $pay_total_tmp + $update["pay_tax"]);
				$update = $sql->setData($table, $update, "pay_adjustment", $pay_adjustment);
				$sql->updateRecord($table, $update, $rec["id"]);
			}

            HttpUtil::location($_SERVER['HTTP_REFERER']);
		}
	}

	static function newColor()
	{
		$image_id = Globals::get("image_id");

		if($cart_id = Globals::get("cart_id"))
		{
			$cart = Globals::session("CART_ITEM");
			if(isset($cart[$cart_id]))
			{
				$tmp = $cart[$cart_id];
				$tmp["cart_id"] = SystemUtil::getUniqId("cart", false, true);
				$tmp["cart_row"] = 1;
				$cart[$tmp["cart_id"]] = $tmp;
				Globals::setSession("CART_ITEM", $cart);
			}
		}

		//カート更新
		refreshCart();

        $url = Extension::getDrawToolLinkString($image_id, $tmp["cart_id"]);

		HttpUtil::location($url);
	}

	static function copyCart()
	{
		if($cart_id = Globals::get("cart_id"))
		{
			$cart = Globals::session("CART_ITEM");
			if(isset($cart[$cart_id]))
			{
				$tmp = $cart[$cart_id];
				$tmp["cart_id"] = SystemUtil::getUniqId("cart", false, true);
				if(!empty($tmp['print_by_layers_data'])) {
				    //Set price = 0 if only the base item choose
				    if($tmp['print_by_layers_activated'] == 1) {
                        $tmp['print_by_layers_data']['price_details']['base_price_for_production'] = "0";
                    }

					//Set copy from to clone item, if copy from clone item, set copy_form to base item
					$tmp['copy_from'] = !empty($cart[$cart_id]['copy_from']) ? $cart[$cart_id]['copy_from'] : $cart_id;
				    $tmp['print_price_tmp'] = $cart[$tmp['copy_from']]['print_by_layers_data']['price_details']['base_price_for_production'];
				}
				$tmp["cart_row"] = 1;

				$cart[$tmp["cart_id"]] = $tmp;
				Globals::setSession("CART_ITEM", $cart);
			}
		}

		//カート更新
		refreshCart();

		// XXX IE でセッション更新が有効とならず、リダイレクトが飛んでこないのでURLも毎回変えて強制リロードを発生
		$rnd = time();

		HttpUtil::location("/page.php?p=cart&".$rnd, true);
	}

	static function delCart()
	{
		if($cart_id = Globals::get("cart_id"))
		{
			$cart = Globals::session("CART_ITEM");
			if(isset($cart[$cart_id]))
			{
				if (isset($cart[$cart_id]["group_id"])) {
					$design_item_count = 0;
					foreach($cart as $key => $val)
					{
						if (isset($val["group_id"]) && $cart[$cart_id]["group_id"] == $val["group_id"]) {
							$design_item_count++;
						}
					}
					if ($design_item_count == 1) {
						$design_item = Globals::session("DESIGN_ITEM");
						if(isset($design_item[$cart[$cart_id]["group_id"]]))
						{
							unset($design_item[$cart_id]);
							Globals::setSession("DESIGN_ITEM", $design_item);
						}

						$design_items = Globals::session("DESIGN_IMAGES");
						if(isset($design_items[$cart[$cart_id]]))
						{
							unset($design_items[$cart_id]);
							Globals::setSession("DESIGN_IMAGES", $design_items);
						}
					}
				}

				// Get plate price if item is silk item
				$platePrice = $cart[$cart_id]['print_by_layers_data']['price_details']['base_price_for_production'];
				if (!empty($cart[$cart_id]['copy_from'])) {
					$platePrice = $cart[$cart[$cart_id]['copy_from']]['print_by_layers_data']['price_details']['base_price_for_production'];
				}
				if ($cart_item_id = self::getPrintLayerCartItem($cart, $cart_id, false)) {
					$cart[$cart_item_id]['print_by_layers_data']['price_details']['base_price_for_production'] = $platePrice;
					$cart[$cart_item_id]['print_price_tmp'] = $platePrice;
				} elseif($cart_item_id = self::getNextCloneCartItem($cart, $cart_id)) {
					$cart[$cart_item_id]['print_by_layers_data']['price_details']['base_price_for_production'] = $platePrice;
					$cart[$cart_item_id]['print_price_tmp'] = $platePrice;
				}
				//If delete base item, then set one clone item to base item
				if(empty($cart[$cart_id]['copy_from'])) {
					$cart = self::setAllCloneCartItemBase($cart, $cart_item_id);
				}
				unset($cart[$cart_id]);
				Globals::setSession("CART_ITEM", $cart);
			}
			$count = 0;
			$newCart = Globals::session("CART_ITEM");
			foreach ($newCart as $val) {
				if($val['item_type'] == 'IT309') {
					$count++;
				}
			}
			if($count < 2) {
				foreach ($newCart as $key => $val) {
					if($val['item_type'] == 'IT309') {
						$newCart[$key]['item_type_size_detail']['ITSI4309']['total'] < 2 ? $newCart[$key]['item_type_size_detail']['ITSI4309']['total'] = 2 : $newCart[$key]['item_type_size_detail']['ITSI4309']['total'];
						Globals::setSession("CART_ITEM", $newCart);
					}
				}
			}
		}

		//カート更新
		refreshCart();

		// XXX IE でセッション更新が有効とならず、リダイレクトが飛んでこないのでURLも毎回変えて強制リロードを発生
		$rnd = time();

		HttpUtil::location("/page.php?p=cart&".$rnd, true);
	}

	/**
	 * Get clone print layer cart item
	 * @param $cart
	 * @param $cart_id
	 * @param bool $include
	 * @return int|null|string
	 */
	static function getPrintLayerCartItem($cart, $cart_id, $include = true)
    {
        $cart_id_target = !empty($cart[$cart_id]['copy_from']) ? $cart[$cart_id]['copy_from'] : $cart_id;
        foreach ($cart as $id => $cart_item) {
            //do not check if not clone item from base item
            if ((isset($cart_item['copy_from']) && $cart_item['copy_from'] != $cart_id_target) || (($cart_id == $id) || $include)) {
                continue;
            }
            if ($cart_item['print_by_layers_activated'] == 1) {
                return $id;
            }
        }
        return null;
    }

	/**
	 * Update all cart item when edit product
	 * @param $cart
	 * @return mixed
	 */
    static function updatePrintPriceCart($cart)
	{
		foreach ($cart as $id => &$cart_item) {
			if(isset($cart_item['copy_from']) && !isset($cart[$cart_item['copy_from']])) {
				if ($cart_item['print_by_layers_activated'] == 1) {
					$cart[$id]['print_by_layers_data']['price_details']['base_price_for_production'] = $cart[$id]['print_price_tmp'];
				}
				$cart = self::setAllCloneCartItemBase($cart, $id);
			}
		}
		return $cart;
	}

	/**
	 * Get next clone cart item
	 * @param $cart
	 * @param $cart_id
	 * @return int|null|string
	 */
    static function getNextCloneCartItem($cart, $cart_id)
	{
		foreach ($cart as $id => $cart_item) {
			if((isset($cart_item['copy_from']) && $cart_item['copy_from'] == $cart_id) && $id != $cart_id)
				return $id;
		}
		return null;
	}

	/**
	 * Update all clone cart copy_from to base cart
	 * @param $cart
	 * @param $cart_id
	 * @return mixed
	 */
	static function setAllCloneCartItemBase($cart, $cart_id)
	{
		$cart_id_target = !empty($cart[$cart_id]['copy_from']) ? $cart[$cart_id]['copy_from'] : $cart_id;
		foreach ($cart as $id => $cart_item) {
			$cart_id_base = !empty($cart_item['copy_from']) ? $cart_item['copy_from'] : $id;
			if ($cart_id == $id || ($cart_id_target != $cart_id_base))
				continue;
			$cart[$id]['copy_from'] = $cart_id;
		}
		unset($cart[$cart_id]['copy_from']);
		return $cart;
	}

	static function appli2web()
	{
		global $sql;
		$checkUser = null;
        $cart_name = 'CART_ITEM';
        $card_thank = 0;
        set_cart_type(false);

        if (!empty(Globals::post("design_from")) && Globals::post("design_from") == 'rakuten') {
            $cart_name = 'CART_ITEM_RAKUTEN';
        }

        if(!empty(Globals::post("card_thank"))){
            $card_thank = Globals::post("card_thank");
        }
		if(Globals::session('LOGIN_TYPE') == 'user' && !empty(Globals::session('LOGIN_ID'))){
            $checkUser = Globals::session('LOGIN_ID');
		}

        if (!empty(Globals::post("session_wish_list"))) {
            //check wish list record
            if (!$wish_list = $sql->selectRecord("wish_list", Globals::post("session_wish_list"))) SystemUtil::errorPage();

            if (empty(Globals::session('wish_list'))) {
                Globals::setSession('CART_ITEM', '');
                Globals::setSession('wish_list', $wish_list['id']);
            } else {
                if (Globals::post("session_wish_list") != Globals::session('wish_list')) {
                    Globals::setSession('CART_ITEM', '');
                    Globals::setSession('wish_list', $wish_list['id']);
                }
            }
        } else {
            if (!empty(Globals::session('wish_list'))) {
                Globals::setSession('CART_ITEM', '');
                Globals::setSession('wish_list', '');
            }
        }

        //パラメータチェック
		if(!$item_type = Globals::post("item_type")) SystemUtil::errorPage();
		else if(!$it_rec = $sql->selectRecord("master_item_type", $item_type)) SystemUtil::errorPage();

		if(!$item_type_sub = Globals::post("item_type_sub")) SystemUtil::errorPage();
		else if(!$itsu_rec = $sql->selectRecord("master_item_type_sub", $item_type_sub)) SystemUtil::errorPage();
		else if($itsu_rec["item_type"] != $it_rec["id"]) SystemUtil::errorPage();

		$table = "master_item_type_size";
		$where = $sql->setWhere($table, null, "item_type", "=", $it_rec["id"]);
		$where = $sql->setWhere($table, $where, "state", "=", 1);
		$where = $sql->setWhere($table, $where, "is_main", "=", 1);
		$order = $sql->setOrder($table, null, "wait", "ASC");
		if($itsi_rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1))))

		if($item_type_size = Globals::post("item_type_size")) {
			$itsi_rec = array('id' => $item_type_size);
		}
		else
		{
			$where = $sql->setWhere($table, $where, "name", "=", "M");
			if($sql->getRow($table, $where))
			{
				$itsi_rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1)));
			}
		}

        if (!empty(Globals::post("design_from")) && Globals::post("design_from") == 'rakuten') {
            $data_design = Globals::post();
            $data_design['item_type_size'] = $itsi_rec["id"];

//            add design session
            $design_session = $sql->setData('design_session', null, "design", Globals::post("image_id"));
            $design_session = $sql->setData('design_session', $design_session, "content", serialize($data_design));
            $design_session = $sql->setData('design_session', $design_session, "regist_unix", time());
            $sql->addRecord("design_session", $design_session);
        }
/*
		if(!$item_type_size = Globals::post("item_type_size")) SystemUtil::errorPage();
		else if(!$itsi_rec = $sql->selectRecord("master_item_type_size", $item_type_size)) SystemUtil::errorPage();
		else if($itsi_rec["item_type"] != $it_rec["id"]) SystemUtil::errorPage();
*/

		if(Globals::post("blank_item")) {
			$image_id = '';
		} else {
			if(!$image_id = Globals::post("image_id")) SystemUtil::errorPage();
		}

		$image_path1 = Globals::post("image_path1");
		$image_path2 = Globals::post("image_path2");
		$image_path3 = Globals::post("image_path3");
		$image_path4 = Globals::post("image_path4");

		$image_preview1 = Globals::post("image_pre1");
		$image_preview2 = Globals::post("image_pre2");
		$image_preview3 = Globals::post("image_pre3");
		$image_preview4 = Globals::post("image_pre4");

		$item_price = $it_rec["item_price"];
		if(Globals::post('blank_item_price')) {
			$item_price = Globals::post('blank_item_price');
		}

		global $FREE_DESIGN_ITEM_ID;
		$tmp_rec3 = $sql->selectRecord("item", Globals::post("item_id"));
        $cart_data = ['has_embroidery' => 0, 'product_type' => TYPE_PRINT];

		if ((Globals::post("item_id") != $FREE_DESIGN_ITEM_ID) && ($tmp_rec3["owner_item"] != $FREE_DESIGN_ITEM_ID && !Globals::post('blank_item'))) { // 無料デザイン商品は後で印刷代を加算する
            if (Globals::post("design_type") == 'new') {
                list($cart_data, $item_price) = updateCartValue($cart_data, Globals::post(), $itsu_rec, $item_price);
            } else {
                if($image_path1) $item_price += $itsu_rec["cost1"];	//表
                if($image_path2) $item_price += $itsu_rec["cost2"];	//裏
                if($image_path3) $item_price += $itsu_rec["cost3"];	//袖
                if($image_path4) $item_price += $itsu_rec["cost3"];	//袖
            }
		}

		//消費税計算
//		$item_price *= 1.08;

		$option_price = 0;

		$option_data = null;
//		if(Globals::post("option_id") && Globals::post("option_price") && Globals::post("option_owner"))
//		{
			$tmp_id = explode(",", Globals::post("option_id"));
			$tmp_price = explode(",",  Globals::post("option_price"));
			$tmp_owner =  explode(",", Globals::post("option_owner"));

			if(count($tmp_id) == count($tmp_price) && count($tmp_price) == count($tmp_owner))
			{
				$option_price_tmp = 0;
				$count_tmp_id = count($tmp_id);
				for($i = 0; $i < $count_tmp_id; $i++)
				{
					if(!$u_rec = $sql->selectRecord("user", $tmp_owner[$i])) $u_rec["id"]=null;
//					if(!is_numeric(1*$tmp_price[$i])) continue;
//					if((1*$tmp_price[$i]) <= 0) continue;

					$option_data[] = array(
						'option_id' => $tmp_id[$i],
						'option_owner' => $u_rec["id"],
						'option_price' => (1*$tmp_price[$i])
					);

					$option_price += (1*$tmp_price[$i]);
				}
			}
//		}

        $printByLayersData = NULL;
        if (Globals::post("print_by_layers_data")) {
            $printByLayersData = json_decode(Globals::post("print_by_layers_data"), true);
        }

        $design_recomment_name = "「".date("Y年n月j日 H:i")."」に作成したデザイン";
        // remove cart pasral
        removeCartPasral(false);
		switch(Globals::post("design_type"))
		{
			case 'new':
				$cart_row_counter = 1;
				$count = 0;
				foreach (Globals::session($cart_name) as $val) {
					if($val['item_type'] == 'IT309') {
						$count++;
					}
				}
				if(!$count) {
					if($item_type == 'IT309') {
						$cart_row_counter = 2;
					}
				}
				$size_id = $itsi_rec["id"];
                $size_detail = '';
				$cart_id = Globals::post("cart_id");
				if ($cart_id) {
					$cart = Globals::session($cart_name);
					if(isset($cart[$cart_id])) {
						$cart_row_counter = $cart[$cart_id]['cart_row'];
						if ($cart[$cart_id]['item_type'] == $it_rec["id"]) {
							$size_id = $cart[$cart_id]['item_type_size'];
							$size_detail = $cart[$cart_id]['item_type_size_detail'];
						}
						unset($cart[$cart_id]);
						Globals::setSession($cart_name, $cart);
					}
				}
                if(empty($size_id)){
                    $tablesize = "master_item_type_size";
                    $where = $sql->setWhere($tablesize, null, "item_type", "=", $it_rec["id"]);
                    $order = $sql->setOrder($tablesize, null, "wait", "ASC");
                    $itsi_rec = $sql->sql_fetch_assoc($sql->getSelectResult($tablesize, $where, $order, array(0, 1)));
                    $size_id = $itsi_rec["id"];
                }
                if(empty($size_detail)){
                    $size_detail = [
                        $size_id => [
                            'item_type_size' => $size_id,
                            'total' => $cart_row_counter,
                        ],
                    ];
                }
				//カート更新
				refreshCart();

                if (empty(Globals::post('item_id'))){
                    $item_id = '';
                }else{
                    $item_id = Globals::post('item_id');
                }

                $cart_data = array_merge($cart_data, array(
					'design_type' => "new",
					'cart_id' => SystemUtil::getUniqId("cart", false, true),
					'cart_row' => $cart_row_counter,
					'cart_price' => $item_price + $option_price,
					'item_id' => $item_id,
					'item_type' => $it_rec["id"],
					'item_type_sub' => $itsu_rec["id"],
					'item_type_size' => $size_id,
					'item_type_size_detail' => $size_detail,
					'item_price' => $item_price,
					'image_id' => $image_id,
					'image_preview1' => $image_preview1,
					'image_preview2' => $image_preview2,
					'image_preview3' => $image_preview3,
					'image_preview4' => $image_preview4,
					'image_path1' => $image_path1,
					'image_path2' => $image_path2,
					'image_path3' => $image_path3,
					'image_path4' => $image_path4,
					'option_data' => $option_data,
					'option_price' => $option_price,
					'design_editable' => 1,
                    'print_by_layers_data' => $printByLayersData,
                    'print_by_layers_activated' => 0,
                    'design_from' => Globals::post("design_from"),
                    'image_design_change_name' => $design_recomment_name,
                    'card_thank' => $card_thank
				));

				if (in_array(Globals::post('endProduct'), ['e', 'pe', 'l', 'pl'])) {
                    $cart_data['product_type'] = Globals::post('endProduct');
                }

                //save  to my design
                if($checkUser  != null){
                    $itemData = getCartItem($checkUser, $cart_data);
                    $cart_data['item_id'] = $itemData['id'];
                    $cart_data['design_type'] = "edit";
                }

				//カート追加
				$cart = Globals::session($cart_name);
				$cart[$cart_data["cart_id"]] = $cart_data;

				//Update cart item price
				$cart = self::updatePrintPriceCart($cart);

				Globals::setSession($cart_name, $cart);

				if(!Globals::session("design_from")){
					Globals::setSession("design_from", Globals::post("design_from"));
				}else if(Globals::session("design_from") ==  'niko1' && !(Globals::post("design_from")=='niko1')){
					Globals::setSession("design_from", Globals::post("design_from"));
				}

				// check redirect to base
				if (Globals::post("shop_id")) {
                    $shop_data = explode('_',Globals::post("shop_id"));
//                    $shop_data[0] is current shop and $shop_data[1] is param in url in base admin
                    if ($shop_data[0] && $shop_data[1]) {
                        $current_shop = $sql->keySelectRecord("personal_shop_info","id", $shop_data[0]);
                        header("location:" . $current_shop['url'] . "/search.php?type=shop_items&shop_id=" . $shop_data[1]);
                        die();
                    }
                }

				//カート更新
				refreshCart();
				if($cart_data["item_type"]=='IT303' || $cart_data["item_type"]=='IT304'
					|| $cart_data["item_type"]=='IT305'  || $cart_data["item_type"]=='IT306'
					|| $cart_data["item_type"]=='IT307'){
					HttpUtil::location("/page.php?p=select-cart-specification&cart_id=".$cart_data["cart_id"], true);
				}
				if(!empty(Globals::post('design_from')) && Globals::post('design_from') == 'rakuten'){
                    HttpUtil::location("/page.php?p=rakuten", true);
                }

                if (Globals::session('LOGIN_TYPE') === 'user' && Globals::post('new') === 'true') {
                    HttpUtil::location(sprintf('/proc.php?run=itemReg&cart_id=%s', $cart_data['cart_id']));
                }

				HttpUtil::location("/page.php?p=cart", true);
				break;

			case 'edit':
			case 'select':
				$cart = Globals::session($cart_name);

				if(!$item_id = Globals::post("item_id")) SystemUtil::errorPage();
				else if(!$i_rec = $sql->selectRecord("item", $item_id)) SystemUtil::errorPage();

                if (($i_rec["item_id"] != $FREE_DESIGN_ITEM_ID) && ($tmp_rec3["owner_item"] != $FREE_DESIGN_ITEM_ID) && !empty($tmp_rec3['embroidery_print'])) {
                    list($cart_data, $item_price) = updateCartValue($cart_data, $tmp_rec3, $itsu_rec, $it_rec["item_price"]);
                }

				if(Globals::post("blank_item")) {
					$i_rec['fee_owner'] = 0;
					$i_rec['fee_user'] = 0;
				}

				if(getSelfFlag($i_rec["id"]) || $i_rec["user"] == Globals::session("LOGIN_ID")){
                    $cart_price = $item_price + $option_price + $i_rec["fee_owner"];
                }

				else
					$cart_price = $item_price + $option_price + $i_rec["fee_user"] + $i_rec["fee_owner"];

				if($cart_row = Globals::post("cart_row")) {} else {
					$cart_row = 1;
				}
				$items = array();
				foreach ($cart as $val) {
					$items[$val['item_id']] = 1;
				}

				if(array_key_exists( Globals::post("item_id"), $items) && $i_rec['item_type_sub'] == $item_type_sub) {
					foreach ($cart as $key => $item) {
						foreach ($item['item_type_size_detail'] as $itemTypeSizeDetail) {
							if(!array_key_exists($itsi_rec["id"], $itemTypeSizeDetail) && $item['item_id'] == Globals::post("item_id")) {
								$cart[$key]['item_type_size_detail'][$itsi_rec["id"]] = [
									'item_type_size' => $itsi_rec["id"],
									'total' => $cart_row
								];
								Globals::setSession($cart_name, $cart);
							}
						}
					}

					//カート更新
					refreshCart();

                    if (!Globals::post('multiple')) {
                        if(!empty(Globals::post('design_from')) && Globals::post('design_from') == 'rakuten'){
                            HttpUtil::location("/page.php?p=rakuten", true);
                        }
                        HttpUtil::location("/page.php?p=cart", true);
                    } else {
                        break;
                    }
				}

            $cart_data = array_merge($cart_data, array(
					'design_type' => Globals::post("design_type"),
					'cart_id' => SystemUtil::getUniqId("cart", false, true),
					'cart_price' => $cart_price,
					'item_id' => $i_rec["id"],
					'item_type' => $it_rec["id"],
					'item_type_sub' => $itsu_rec["id"],
					'item_type_size_detail' => [
						$itsi_rec["id"] => [
							'item_type_size' => $itsi_rec["id"],
							'total' => $cart_row,
						],
					],
					'item_type_size' => $itsi_rec["id"],
					'item_price' => $item_price,
					'image_id' => $image_id,
					'image_preview1' => $image_preview1,
					'image_preview2' => $image_preview2,
					'image_preview3' => $image_preview3,
					'image_preview4' => $image_preview4,
					'image_path1' => $image_path1,
					'image_path2' => $image_path2,
					'image_path3' => $image_path3,
					'image_path4' => $image_path4,
					'option_data' => $option_data,
					'option_price' => $option_price,
					'design_editable' => (($i_rec['owner_item'] == '') && ($i_rec['2nd_state'] == 1) &&
						($i_rec['cart_state'] == 0)) ? 1 : 0,
                    'print_by_layers_data' => $printByLayersData,
                    'print_by_layers_activated' => 0,
					'design_from' => Globals::post("design_from"),
					'card_thank' => $card_thank
				));

                if (in_array(Globals::post('endProduct'), ['e', 'pe', 'l', 'pl'])) {
                    $cart_data['product_type'] = Globals::post('endProduct');
                } elseif (!empty($i_rec['product_type'])) {
                    $cart_data['product_type'] = $i_rec['product_type'];
                }
				if(Globals::post('blank_item')) {
					$cart_data['blank_item'] = 1;
					$cart_data['item_type_size_detail'][$itsi_rec["id"]]['product_type'] = 'blank';
				}
				//カート追加
				$cart = Globals::session($cart_name);
				$cart[$cart_data["cart_id"]] = $cart_data;

				//Update cart item price
				$cart = self::updatePrintPriceCart($cart);

				Globals::setSession($cart_name, $cart);
				if(!Globals::session("design_from")){
					Globals::setSession("design_from", Globals::post("design_from"));
				}else if(Globals::session("design_from") ==  'niko1' && !(Globals::post("design_from")=='niko1')){
					Globals::setSession("design_from", Globals::post("design_from"));
				}

				//カート更新
				refreshCart();
			if($cart_data["item_type"]=='IT303' || $cart_data["item_type"]=='IT304'
				|| $cart_data["item_type"]=='IT305'  || $cart_data["item_type"]=='IT306'
				|| $cart_data["item_type"]=='IT307'){
                    if (!Globals::post('multiple')) {
                        HttpUtil::location("/page.php?p=select-cart-specification&cart_id=" . $cart_data["cart_id"], true);
                    }
				}
                if (!Globals::post('multiple')) {
                    if(!empty(Globals::post('design_from')) && Globals::post('design_from') == 'rakuten'){
                        HttpUtil::location("/page.php?p=rakuten", true);
                    }
                    HttpUtil::location("/page.php?p=cart", true);
                }
				break;
		}

        if (!Globals::post('multiple')) {
            SystemUtil::errorPage();
        }
	}

	static function appliPasral(){

        global $sql;
        $checkUser = null;
        $cart_name = 'CART_ITEM';
        set_cart_type(false);

        if(Globals::session('LOGIN_TYPE') == 'user' && !empty(Globals::session('LOGIN_ID'))){
            $checkUser = Globals::session('LOGIN_ID');
        }

        $cart_data = ['has_embroidery' => 0, 'product_type' => RINGPASRAL];

        $ring_pasral = Globals::post();
        $owner_item = null;
        $owner_user = null;
        $pasral_design_again = 0;
        $fee_owner_pasral = 0;

        if(!empty($ring_pasral['parent_id'])){
            if($item_rec = $sql->selectRecord("item",$ring_pasral['parent_id']))
            {
                if($item_rec['user'] != Globals::session("LOGIN_ID")){
                    $owner_item = $ring_pasral['parent_id'];
                    $pasral_design_again = 1;
                    $fee_owner_pasral = $item_rec['fee_user'];
                    $owner_user = $item_rec['user'];
                }
            }
        }
        if(empty($ring_pasral)) SystemUtil::errorPage();

        $cart_data = array_merge($cart_data, array(
            'design_type' => 'new',
            'cart_id' => SystemUtil::getUniqId("cart", false, true),
            'cart_row' => 1, // count of design
            'cart_price' => $ring_pasral['price'] + $fee_owner_pasral,
            'item_id' => null,
            'item_type' => null,
            'design_name' => $ring_pasral['name'],
            'ring_type' => $ring_pasral['ring_type'],
            'ring_size' => $ring_pasral['ring_size'],
            'ring_color' => $ring_pasral['ring_color'],
            'ring_name' => $ring_pasral['ring_name'],
            'ring_size_text' => $ring_pasral['ring_size_text'],
            'item_type_sub' => null,
            'item_type_size_detail' => [
                [
                    'item_type_size'=> null,
                    'total' => 1// count of design
                ]
            ],
            'item_type_size' => null,
            'item_price' => $ring_pasral['price'],
            'image_id' => null,
            'image_preview1' => $ring_pasral['image'],
            'image_preview2' => null,
            'image_preview3' => null,
            'image_preview4' => null,
            'image_path1' => null,
            'image_path2' => null,
            'image_path3' => null,
            'image_path4' => null,
            'option_data' => null,
            'option_price' => null,
            'design_editable' => 0,
            'print_by_layers_data' => null,
            'print_by_layers_activated' => 0,
            'number_image' => $ring_pasral['number_image'],
            'hash_code_pasral' => $ring_pasral['hash_code'],
            'design_id_pasral' => $ring_pasral['sku'],
            'ring' => $ring_pasral['ring'],
            'product_sizes' => $ring_pasral['product_sizes'],
            'design_from' => Globals::post("design_from"), // from pasral
            'owner_item' => $owner_item,
            'pasral_design_again' =>  $pasral_design_again,
            'fee_owner' => $fee_owner_pasral,
            'owner' => $owner_user,
        ));

        removeCartPasral(true);

        //insert pay_item if user login
        if($checkUser  != null){
            $itemData = getCartItem($checkUser, $cart_data);
            $cart_data['item_id'] = $itemData['id'];
            $cart_data['design_type'] = "edit";
        }

        $cart = Globals::session($cart_name);
        if(!empty($ring_pasral['cart_id']) && !empty($cart[$ring_pasral['cart_id']])){
            $cart_data['cart_row'] = $cart[$ring_pasral['cart_id']]['cart_row'];
            $cart_data['item_type_size_detail'][0]['total'] = $cart[$ring_pasral['cart_id']]['item_type_size_detail'][0]['total'];
            unset($cart[$ring_pasral['cart_id']]);
        }
        $cart[$cart_data["cart_id"]] = $cart_data;
        Globals::setSession($cart_name, $cart);
        refreshCart();

        HttpUtil::location("/page.php?p=cart", true);

    }

    static function appliPasralAgain(){
        global $sql;

        if(!$item_id = Globals::get("id")) SystemUtil::errorPage();
        $item = $sql->selectRecord("item", $item_id);
        if(!$item) SystemUtil::errorPage();

        set_cart_type(false);

        try {
            $design_pasral = getDataApiPasral($item["design_id_pasral"], $item["hash_code_pasral"]);
        } catch (Exception $exception) {
            SystemUtil::errorPage();
        }

        if(empty($design_pasral) || $design_pasral['status'] == 0){
            SystemUtil::errorPage();
        }

        $cart_name = 'CART_ITEM';
        $cart = Globals::session($cart_name);

        foreach ($cart as $cart_item){
            if(!empty($cart_item['item_id']) && $cart_item['item_id'] == $item){
                return ;
            }
        }

        $cart_data = ['has_embroidery' => 0, 'product_type' => RINGPASRAL];

        $cart_row = !empty(Globals::get("quantity")) ? Globals::get("quantity") : 1;
        $cart_data = array_merge($cart_data, array(
            'design_type' => 'select',
            'cart_id' => SystemUtil::getUniqId("cart", false, true),
            'cart_row' => $cart_row, // count of design
            'cart_price' => $item['item_price'],
            'item_id' => $item['id'],
            'item_type' => null,
            'design_name' => $item['name'],
            'ring_name' => $item['ring_name'],
            'ring_type' => $item['ring_type'],
            'ring_color' => $item['ring_color'],
            'ring_size' => $item['ring_size'],
            'item_type_sub' => null,
            'item_type_size_detail' => [
                [
                    'item_type_size'=> null,
                    'total' => $cart_row// count of design
                ]
            ],
            'item_type_size' => null,
            'item_price' => $item['item_price'],
            'image_id' => null,
            'image_preview1' => $item['item_preview1'],
            'image_preview2' => null,
            'image_preview3' => null,
            'image_preview4' => null,
            'image_path1' => null,
            'image_path2' => null,
            'image_path3' => null,
            'image_path4' => null,
            'option_data' => null,
            'option_price' => null,
            'design_editable' => 0,
            'print_by_layers_data' => null,
            'print_by_layers_activated' => 0,
            'number_image' => $item['number_image'],
            'hash_code_pasral' => $item['hash_code_pasral'],
            'design_id_pasral' => $item['design_id_pasral'],
            'product_sizes' => $design_pasral['data']['product_sizes'],
            'design_from' => RINGPASRAL, // from pasral
        ));

        $cart[$cart_data["cart_id"]] = $cart_data;

        Globals::setSession($cart_name, $cart);
        refreshCart();
        removeCartPasral(true);

    }

	static function agency2user()
	{
		global $sql;

		if(!$rec = $sql->selectRecord("user", Globals::get("id"))) SystemUtil::errorPage();
		if($rec["agency"] != Globals::session("LOGIN_ID")) SystemUtil::errorPage();

        // Remove admin permission
        Globals::setSession("MAIN_LOGIN_TYPE", '');
        Globals::setSession("ADMIN", false);

        //ゴッドモード離脱用にセッションにIDを保存
		Globals::setSession("GOD_LOGIN_TYPE", Globals::session("LOGIN_TYPE"));
		Globals::setSession("GOD_LOGIN_ID", Globals::session("LOGIN_ID"));

		//IDを書き換え
		Globals::setSession("LOGIN_TYPE", "user");
		Globals::setSession("LOGIN_ID", $rec["id"]);

		HttpUtil::location("/");
	}

	static function social()
	{
		global $sql;

		require_once("./module/module/hybridauth/Hybrid/Auth.php");
		$baseurl = '';
		if(!empty(Globals::get("social_type")) && Globals::get("social_type")=='Facebook'){
            $baseurl = "https://ondemand.cbox.nu/module/module/hybridauth/";
        }
        else
        {
            $baseurl = "http://ondemand.cbox.nu/module/module/hybridauth/";
        }
		$config = array(
			'base_url' => $baseurl,
			'providers' => array(
				"Google" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "829646703211-d0rmu58genopvv03696o3t4kqvlvsfbd.apps.googleusercontent.com", "secret" => "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" )
				),
				 "Facebook" => array (
				 	"enabled" => true,
				 	"keys"    => array ( "id" => "341116694462137", "secret" => "8f631d23469078e42074e0b8d46a17e3" ),
				 	"trustForwarded" => false,
				 ),
				"Instagram" => array (
					"enabled" => true,
					"keys"    => array ( "id" => "346792670702371", "secret" => "283d34a888eae451b30f769c8d3e8833" )
				),
				"Twitter" => array (
					"enabled" => true,
					"keys"    => array ( "key" => "1gTirl1lauO4y9dwhSnV4Glg1", "secret" => "Hqyx6AI4QLhJTve45yAWSfXGWcuAKXhAnGdGoVzzREYKBPcpuh" )
				),
			),
			"debug_mode" => false,
			"debug_file" => "./log/HybridAuth.txt"
		);

		//social定義確認
		if(!$social_type = Globals::get("social_type")) return;
		if(!isset($config["providers"][$social_type])) return;

		$auth = new Hybrid_Auth($config);
		$client = $auth->authenticate($social_type);

		$user_profile = $client->getUserProfile();

		$ret = array("social_id" => "", "name" => "", "mail" => "");
		if(isset($user_profile->identifier)) $ret["social_id"] = $user_profile->identifier;
		if(isset($user_profile->displayName)) $ret["name"] = $user_profile->displayName;
		if(isset($user_profile->email)) $ret["mail"] = $user_profile->email;

		if(!$ret["social_id"]) return;

		//アカウント確認
		$table = "user";
		$where = $sql->setWhere($table, null, "social_type", "=", $social_type);
		$where = $sql->setWhere($table, $where, "social_id", "=", $ret["social_id"]);
		$where = $sql->setWhere($table, $where, "state", "=", 1);
		$order = $sql->setOrder($table, null, "edit_unix", "DESC");
		if($rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1))))
		{
			//既存アカウント
			Globals::setSession("LOGIN_TYPE", "user");
			Globals::setSession("LOGIN_ID", $rec["id"]);

			$table = "user";
			$update = $sql->setData($table, null, "login_unix", time());
			$sql->updateRecord($table, $update, $rec["id"]);
            changeStatusLogin(null, $rec["id"]);

			HttpUtil::location("/");
		}
		else
		{
			//新規アカウント作成
			$ret["page"] = "check";
			$ret["TOKEN_CODE"] = SystemUtil::setTokenCode("regist", "user");
			Globals::setSession("SOCIAL_TYPE", $social_type);
			Globals::setSession("SOCIAL_ID", $ret["social_id"]);
			HttpUtil::postLocation("/regist.php?type=user", $ret);
		}
	}

	static function passEdit()
	{
		global $sql;

		if(!$key = Globals::get("key")) SystemUtil::errorPage();

		$tmp = TextUtil::decrypt(base64_decode($key), "passstr");
		if(strpos($tmp, "|") === false) SystemUtil::errorPage();

		list($id, $table) = explode("|", $tmp);
		if(!$rec = $sql->selectRecord($table, $id)) SystemUtil::errorPage();

		//アカウント状態チェック
		if($rec["state"] != 1) SystemUtil::errorPage();

		Globals::setSession("LOGIN_TYPE", $table);
		Globals::setSession("LOGIN_ID", $rec["id"]);

		HttpUtil::location("/edit.php?type=".$table."&design=pass");
	}

	static function passReminder()
	{
		global $sql;
		global $cc;

		$table = "user";

		$mail = Globals::get("mail");

		if(!CheckUtil::is_mail($mail)){ $data["state"] = 0; echo json_encode($data); exit; }
		else if(CheckUtil::is_doubles($table, "mail", $mail)){ $data["state"] = 0; echo json_encode($data); exit; }

		$rec = $sql->keySelectRecord($table, "mail", $mail);

		$cc->setVariable("key", urlencode(base64_encode(TextUtil::encryption($rec["id"]."|".$table, "passstr"))));

		//メール送信
		mail_templateFunc::sendMail("nobody", "password", $rec["mail"], $rec);

		$data["state"] = 1;
		echo json_encode($data); exit;
	}

	static function registAfterShip()
	{
		global $sql;

		$data = array();

		$al_table = "after_log";
		if(!$id = Globals::get("id")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!$slipno = Globals::get("slipno")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!$pdcompanycode = Globals::get("pdcompanycode")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!is_numeric($pdcompanycode)){ $data["state"] = 0; echo json_encode($data); exit; }

		if(!$p_rec = $sql->selectRecord("pay", $id)){ $data["state"] = 0; echo json_encode($data); exit; }

		if(!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $p_rec["id"])){ $data["state"] = 0; echo json_encode($data); exit; }

		$update = $sql->setData($al_table, null, "slipno", $slipno);
		$update = $sql->setData($al_table, $update, "pdcompanycode", $pdcompanycode);
		$sql->updateRecord($al_table, $update, $al_rec["id"]);

		$data["state"] = 1;
		echo json_encode($data); exit;
	}
	static function registAfterShip2()
	{
		global $sql;

		$data = array();

		$al_table = "after_log2";
		if(!$id = Globals::get("id")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!$slipno = Globals::get("slipno")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!$pdcompanycode = Globals::get("pdcompanycode")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!is_numeric($pdcompanycode)){ $data["state"] = 0; echo json_encode($data); exit; }

		if(!$p_rec = $sql->selectRecord("pay", $id)){ $data["state"] = 0; echo json_encode($data); exit; }

		if(!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $p_rec["id"])){ $data["state"] = 0; echo json_encode($data); exit; }

		$update = $sql->setData($al_table, null, "slipno", $slipno);
		$update = $sql->setData($al_table, $update, "pdcompanycode", $pdcompanycode);
		$sql->updateRecord($al_table, $update, $al_rec["id"]);

		$data["state"] = 1;
		echo json_encode($data); exit;
	}

	static function editPayItem()
	{
        editPayItem();

	    HttpUtil::location("/edit.php?type=pay&id=".Globals::get("id")."&design=pay");
	}

    static function editPayPasralItem()
    {
        editPayPasralItem();

        HttpUtil::location("/edit.php?type=pay_pasral&id=".Globals::get("id")."&design=pay_pasral");
    }

	static function changeCartUnit()
	{
		global $sql;

		$pay = Globals::post("pay");
		$pay_item_id = Globals::post("pay_item_id");

		$check = true;

		if(!$item_type = Globals::post("item_type")) $check = false;
		if(!$item_type_sub = Globals::post("item_type_sub")) $check = false;
		if(!$item_type_size = Globals::post("item_type_size")) $check = false;
		if(!$cart_row = Globals::post("cart_row")) $check = false;

		if(!is_null($formId = Globals::get('rmFormId'))) {
			$check = false;
			$data['canReload'] = false;
			$cart = Globals::session("CART_ITEM");
			if(key_exists($formId, $cart)){
				unset($cart[$formId]);
				Globals::setSession("CART_ITEM", $cart);
				$data['canReload'] = true;
			}

			jsonEncode($data);
		}

		if($check)
		{
			$cart = Globals::session("CART_ITEM");

			if(!$mit_rec = $sql->selectRecord("master_item_type", $item_type)) $check = false;
			if(!$mit_sub_rec = $sql->selectRecord("master_item_type_sub", $item_type_sub)) $check = false;
			if(!$mit_size_rec = $sql->selectRecord("master_item_type_size", $item_type_size)) $check = false;
			if(!$item = Globals::post("item")) $check = false;
			if(!$current_pay_item_id = Globals::post("current_pay_item_id")) $check = false;

			if(!is_numeric($cart_row))
			{
				$check = false;
			}
			else if($cart_row <= 0)
			{
				$check = false;
			}

			if($check)
			{
				$cart[$pay_item_id]['item'] = $item;
				$cart[$pay_item_id]['pay_item_id'] = $pay_item_id;
				$cart[$pay_item_id]["item_type"] = $item_type;
				$cart[$pay_item_id]["item_type_sub"] = $item_type_sub;
				$cart[$pay_item_id]["item_type_size"] = $item_type_size;
				$cart[$pay_item_id]["cart_row"] = $cart_row;
				$cart[$pay_item_id]["current_pay_item_id"] = $current_pay_item_id;
				Globals::setSession("CART_ITEM", $cart);
			}
		}

		HttpUtil::location("/edit.php?type=pay&id=".$pay."&design=pay");
	}

    static function changeCartUnitPasral()
    {
        global $sql;

        $pay = Globals::post("pay");
        $pay_item_id = Globals::post("pay_item_id");

        $check = true;

        if(!$cart_row = Globals::post("cart_row")) $check = false;

        if(!is_null($formId = Globals::get('rmFormId'))) {
            $check = false;
            $data['canReload'] = false;
            $cart = Globals::session("CART_ITEM_PASRAL");
            if(key_exists($formId, $cart)){
                unset($cart[$formId]);
                Globals::setSession("CART_ITEM_PASRAL", $cart);
                $data['canReload'] = true;
            }

            jsonEncode($data);
        }

        if($check)
        {
            $cart = Globals::session("CART_ITEM_PASRAL");

            if(!$item = Globals::post("item")) $check = false;
            if(!$current_pay_item_id = Globals::post("current_pay_item_id")) $check = false;

            if(!is_numeric($cart_row))
            {
                $check = false;
            }
            else if($cart_row <= 0)
            {
                $check = false;
            }

            if($check)
            {
                $cart[$pay_item_id]['item'] = $item;
                $cart[$pay_item_id]['pay_item_id'] = $pay_item_id;
                $cart[$pay_item_id]["cart_row"] = $cart_row;
                $cart[$pay_item_id]["current_pay_item_id"] = $current_pay_item_id;
                $cart[$pay_item_id]["order_id"] = $pay;
                $cart[$pay_item_id]["item_type"] = '';
                $cart[$pay_item_id]["item_type_sub"] = '';
                $cart[$pay_item_id]["item_type_size"] = '';
                Globals::setSession("CART_ITEM_PASRAL", $cart);
            }
        }

        HttpUtil::location("/edit.php?type=pay_pasral&id=".$pay."&design=pay_pasral");
    }

	static function upload_csv_delivery() {
		if(!CheckUtil::is_file_error("csv"))
		{
			Globals::setSession("import_message", "アップロードサイズを小さくしてください。");
			return;
		}

		$data_type = Globals::post("data_type");

		$import_file = SystemUtil::doFileUpload(Globals::files("csv"));
		if(is_file($import_file)) {
			$extension = pathinfo($import_file, PATHINFO_EXTENSION);
			if ($extension != "csv") {
				Globals::setSession("import_message", "CSVファイルをアップロードしてください。1");
				unlink($import_file);
				return;
			}
		}

		$dataOrder = [];
		if(($handle = fopen($import_file, "r")) !== false)
			{
				$count = 0;
				while(($data = fgetcsv_reg($handle, 8000, ",")) !== false)
				{
					$count++;
					mb_convert_variables(mb_internal_encoding(), "SJIS-win", $data);

                    if (!empty(Globals::post("product_type")) && Globals::post("product_type") == 'mask') {
                        $customer_number = $data[1];
                        $tracking_number = $data[0];
                    } else {
                        //１行目スキップ
                        if ($count == 1) continue;

                        if ($data_type == 'yamato') {
                            $customer_number = $data[7];
                            $tracking_number = $data[3];
                        } else {
                            $customer_number = $data[1];
                            $tracking_number = $data[31];
                        }
                    }

                    if ($data_type == 'yamato') {
                        $delivery_service = 'yamato_marui';
                    } else {
                        $delivery_service = 'sagawa_marui';
                    }

                    $dataOrder['orders'][] = [
                        'Order' => [
                            'customer_number' => $customer_number,
                            'tracking_number' => $tracking_number,
                            'delivery_service' => $delivery_service
                        ]
                    ];
				}
			}

		$data_string = json_encode($dataOrder);

		$ch = curl_init(ApiConfig::HOST . '/orders/tracking');
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);

        if(!curl_errno($ch)) {
            $info = curl_getinfo($ch);
            if ($info['http_code'] == 200){
                Globals::setSession("import_message", 'ファイルのアップロードに成功しました。');
            } else {
				$message = json_decode($result)->message;
				Globals::setSession("import_message", $message);
			}
        } else {
        	$message = "ファイルのアップロードに失敗しました。";
            Globals::setSession("import_message", $message);
        }

        unlink($import_file);

        curl_close($ch);
	}

    static function import_stock_markless()
    {
        global $sql;

        $data_vendor = 'markless';
        $data_type = 'item_stock';
        $insert_count = 0;
        $data_count = 0;
        $error_count = 0;
        $error_text = "";
        var_dump('start');
        $url = 'http://api.beta.printty.maruig.com/external/markless/stock?APIKey=61f0c535-cc5c-422f-8158-7d9dac1f1c38';

        $client = new \GuzzleHttp\Client();
        try {
            $response = $client->request('GET', $url);
        } catch (Exception $exception) {
            var_dump('false');
            return false;
        }
        $dataPrintty = json_decode($response->getBody()->getContents(), true);
        $dt = $dataPrintty['stock_individuals'];
        
            //全削除
            $table = "item_stock";
            $where = $sql->setWhere($table, null, "vendor", "=", $data_vendor);
            $sql->deleteRecordWhere($table, $where);
            $sql->deleteRecordWhere('blank_item_stock', $where);

                foreach($dt as $count => $data){
                    var_dump('data api: ---------------');
                    var_dump($data);
                    //Map data base on CSV type
                    $expected_import_date = '未定';
                    $stock_num = $data['stock_quantity'] ? $data['stock_quantity'] : 0;
                    $stock_item = $data['product_code'];
                    if($data['proximate_scheduled_arrival_date']) {
                        $expected_import_date = str_replace('-', '/', $data['proximate_scheduled_arrival_date']);
                    }

//                    $stock_num = $stock_num + $data['factory_stock'];
                    $data_count++;


                    $where1 = $sql->setWhere("printty_products", null, "id", "=", $data['product_id']);
                    $printty_rec = $sql->sql_fetch_assoc($sql->getSelectResult('printty_products', $where1));
                    if(!$printty_rec) {
                        $where = $sql->setWhere("master_item_type", null, "item_code", "=", $stock_item);
                        $stock_rec = $sql->getSelectResult("master_item_type", $where);
                        if ($stock_rec->num_rows == 0) {
                            $where = $sql->setWhere("master_item_type", null, "item_code_nominal", "=", $stock_item);
                            $stock_rec = $sql->getSelectResult("master_item_type", $where);
                            if ($stock_rec->num_rows == 0) {
                                $where = $sql->setWhere("master_item_type", null, "item_code_nominal", "=", str_replace('-' . $data['color_code'], '', $stock_item));
                                $stock_rec = $sql->getSelectResult("master_item_type", $where);
                            }
                        }
                    }else{
                        $where = $sql->setWhere("master_item_type", null, "item_code", "=", $printty_rec['product_code']);
                        $stock_rec = $sql->getSelectResult("master_item_type", $where);
                    }
                    //追加データ
                    $table = $data_type;
                    while($rec = $sql->sql_fetch_assoc($stock_rec)) {

                        //Map data size and color base on CSV type
                        $color_code = $data['color_code'];
                        $size_code = $data['size_code'];

                        //Get item
                        $where = $sql->setWhere("master_item_type_size", null, "item_type", "=", $rec['id']);
                        $item_rec = $sql->sql_fetch_assoc($sql->getSelectResult('master_item_type_size', $where));
                        if(!$item_rec){
                            $size_code = $data['size_code'];
                        }else{
                            $size_code = $item_rec['item_code'];
                        }


                        $is_rec = $sql->setData($table, null, "id", SystemUtil::getUniqId($table, false, true));
                        $is_rec = $sql->setData($table, $is_rec, "item", $rec['item_code']);
                        $is_rec = $sql->setData($table, $is_rec, "item_type_sub_code", $color_code);
                        $is_rec = $sql->setData($table, $is_rec, "item_type_size_code", $size_code);
                        $is_rec = $sql->setData($table, $is_rec, "stock", $stock_num);
                        $is_rec = $sql->setData($table, $is_rec, "state", 1);
                        $is_rec = $sql->setData($table, $is_rec, "vendor", $data_vendor);

                        var_dump($rec['item_code']);
                        var_dump($color_code);
                        var_dump($size_code);
                        var_dump($stock_num);
                        var_dump('=====================================');

                        if(!empty($color_code) && !empty($size_code) && $stock_num < 3) {
                            var_dump('---stock---');
                            $sql->addRecord($table, $is_rec);
                        }
                        if($stock_num < 3) {
                            $insert_count++;
                        }
                        $blankItemStock = $sql->setData('blank_item_stock', null, "id", SystemUtil::getUniqId($table, false, true));
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_code", $rec['item_code']);
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_sub_code", $color_code);
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_size_code", $size_code);
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "stock", $stock_num);
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "expected_import_date", $expected_import_date);
                        $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "vendor", $data_vendor);

                        var_dump($rec['item_code']);
                        var_dump($color_code);
                        var_dump($size_code);
                        var_dump($rec['state']);
                        var_dump('=====================================');

                        if(!empty($color_code) && !empty($size_code) && $rec['state'] == 1) {
                            var_dump('---blank---');
                            $sql->addRecord('blank_item_stock', $blankItemStock);
                        }
                    }
                }

//                fakeStock();
//                fakeStock('IT368', '"ITSI5392","ITSI5391","ITSI5389","ITSI5390","ITSI5394","ITSI5395","ITSI5388"');


            $message_tmp = "";

            if($error_text) $error_text = "（".rtrim($error_text, "、")."）";

            $message = $data_count."件中／".$error_count."件のエラー".$error_text."と".$insert_count."件の追加を行いました。";

//            self::updateBlankItemStockNumber();
//        var_dump($message);die;
            return;
    }

    static function import_stock($data_vendor = '', $import_file = '')
    {
        global $sql;

        $data_type = 'item_stock';

        if (empty($data_vendor)) {
            $data_vendor = Globals::post("data_vendor");
        }

        if (empty($import_file)) {
            $import_file = SystemUtil::doFileUpload(Globals::files("csv"));
        }

        if(is_file($import_file))
        {
            $extension = pathinfo($import_file, PATHINFO_EXTENSION);
            if(Globals::files("csv") && $extension != "csv")
            {
                Globals::setSession("import_message", "CSVファイルをアップロードしてください。1");
                unlink($import_file);
                return;
            }

            $insert_count = 0;
            $data_count = 0;
            $error_count = 0;
            $error_text = '';
            $stock_index = 0;
            $plain_index = 0;
            $master_item_type_subs = [];
            $master_item_type_data = [0];
            $master_item_type_sizes = [];
            $master_item_types = get_field_data('master_item_type', 'item_code', 'id', '', $master_item_type_data);
            $master_item_stock_types = get_field_data('master_item_stock_type', 'stock_item', 'id', 'stock_item');
            $insert_item_stock = '';
            $insert_plain_item_stock = '';
            $insert_item_stock_query = 'INSERT INTO `item_stock`(`id`, `item`, `item_type_sub_code`, `item_type_size_code`, `stock`, `state`, `vendor`) VALUES %s;';
            $insert_plain_item_stock_query = 'INSERT INTO `blank_item_stock`(`id`, `item_code`, `item_sub_code`, `item_size_code`, `stock`, `expected_import_date`, `vendor`) VALUES %s;';

			//全削除
			$table = "item_stock";
			$where = $sql->setWhere($table, null, "vendor", "=", $data_vendor);
			$sql->deleteRecordWhere($table, $where);
			$sql->deleteRecordWhere('blank_item_stock', $where);

            //インポート処理
            if(($handle = fopen($import_file, "r")) !== false)
            {
                $count = 0;

                if (in_array($data_vendor, ['felic', 'cab', 'bonmax'])) {
                    $master_item_type_subs  = get_field_data('master_item_type_sub', 'vendor_color_code', 'vendor_color_code', 'item_type');
                    $master_item_type_sizes = get_field_data('master_item_type_size', 'vendor_size_code', 'vendor_size_code', 'item_type');
                }

                while(($data = fgetcsv_reg($handle, 8000, ",")) !== false)
                {
                    $count++;
                    $stock_num = '';
                    $stock_item = '';
                    mb_convert_variables(mb_internal_encoding(), "SJIS-win", $data);

                    //Map data base on CSV type
                    $expected_import_date = '未定';
                    switch ($data_vendor) {
                        case 'tom':
                            $stock_num = $data[8];
                            $stock_item = $data[1];
                            if($data[10]) {
                                $expected_import_date = substr($data[10], 0, 4) . '/' . substr($data[10], 4, 2) . '/' . substr($data[10], 6, 2);
                            }
                            break;
                        case 'cab':
                            $expected_import_date = '未定';
                            $stock_num = str_replace(',','',$data[6]);
                            $stock_item = $data[0];
                            break;
                        case 'felic':
                            $stock_num = $data[6];
                            $stock_item = $data[1];
                            if($stock_num == 0) {
                                $expected_import_date = $data[8];
                            }
                            break;

                        case 'bonmax':
                            $stock_num = $data[7];
                            $stock_item = $data[1];
                            break;

                        case 'wundou':
                            if($data[4] < 0){
                                $data[4] = 0;
                            }
                            $stock_num = $data[4];
                            $stock_item = trim($data[1]);
                            break;
                    }

                    //１行目スキップ
                    if($count == 1)
                    {
                        if(Globals::post("one")) continue;
                    }

                    if(!is_array($data)) continue;

                    $data_count++;

                    //在庫数チェック
                    if($stock_num === ""){ $error_count++; $error_text .= $count."行目[空]、"; continue; }
                    if(!is_numeric($stock_num)){ $error_count++; $error_text .= $count."行目[フォーマット]、"; continue; }

                    //追加データ
                    $table = $data_type;
                    $color_code = '';
                    $size_code = '';
                    $item_codes = [];
                    $item_code_done = [];
                    $stock_item_no_zero = ltrim($stock_item, '0');

                    if ($stock_item == 594201 && $stock_num < 3) {
                        $a = 1;
                    }

                    if (!empty($master_item_stock_types[$stock_item])) {
                        $item_codes = $master_item_stock_types[$stock_item];
                    } elseif (!empty($master_item_stock_types[$stock_item_no_zero])) {
                        $item_codes = $master_item_stock_types[$stock_item_no_zero];
                    }

                    foreach ($item_codes as $item_code) {
                        if (!empty($item_code_done[$item_code]) || !isset($master_item_types[$item_code])) {
                            continue;
                        }

                        $item_code_done[$item_code] = 1;

                        //Map data size and color base on CSV type
                        switch ($data_vendor) {
                            case 'tom':
                                $color_code = $data[3];
                                $size_code = $data[5];
                                break;
                            case 'cab':
                                $color_code = $data[2];
                                $size_code = $data[4];
                                break;
                            case 'felic':
                                $color_code = $data[2];
                                $size_code = $data[5];
                                break;
                            case 'bonmax':
                                $color_code = $data[3];
                                $size_code = $data[5];
                                break;
                            case 'wundou':
                                $color_code = trim($data[2]);
                                $size_code = trim($data[3]);
                                break;
                        }

                        $size_code_no_zero = ltrim($size_code, '0');
                        $color_code_no_zero = ltrim($color_code, '0');
                        $item_type_id = $master_item_types[$item_code];

                        if(in_array($data_vendor, ['felic', 'cab', 'bonmax'])) {
                            if (!empty($master_item_type_sizes[$item_type_id][$size_code])) {
                                $size_code = $master_item_type_sizes[$item_type_id][$size_code];
                            } elseif (!empty($master_item_type_sizes[$item_type_id][$size_code_no_zero])) {
                                $size_code = $master_item_type_sizes[$item_type_id][$size_code_no_zero];
                            } else {
                                $size_code = '';
                            }

                            if (!empty($master_item_type_subs[$item_type_id][$color_code])) {
                                $color_code = $master_item_type_subs[$item_type_id][$color_code];
                            } elseif (!empty($master_item_type_subs[$item_type_id][$color_code_no_zero])) {
                                $color_code = $master_item_type_subs[$item_type_id][$color_code_no_zero];
                            } else {
                                $color_code = '';
                            }
                        }

                        if(!empty($color_code) && !empty($size_code) && $stock_num < 3) {
                            $is_rec = $sql->setData($table, null, "id", SystemUtil::getUniqId($table, false, true));
                            $is_rec = $sql->setData($table, $is_rec, "item", $item_code);
                            $is_rec = $sql->setData($table, $is_rec, "item_type_sub_code", $color_code);
                            $is_rec = $sql->setData($table, $is_rec, "item_type_size_code", $size_code);
                            $is_rec = $sql->setData($table, $is_rec, "stock", $stock_num);
                            $is_rec = $sql->setData($table, $is_rec, "state", 1);
                            $is_rec = $sql->setData($table, $is_rec, "vendor", $data_vendor);
                            insert_or_update($stock_index, $is_rec, $insert_item_stock_query, $insert_item_stock);
                            if (in_array($item_code, constants::FAKE_ITEM)) {
                                $is_rec['id'] = SystemUtil::getUniqId($table, false, true);
                                $is_rec['item'] = constants::FAKE_ITEM_STOCK[$item_code];
                                insert_or_update($stock_index, $is_rec, $insert_item_stock_query, $insert_item_stock);
                            }
                        }

                        if($stock_num < 3) {
                            $insert_count++;
                        }

                        if(!empty($color_code) && !empty($size_code) && $master_item_type_data['states'][$item_type_id] == 1) {
                            $blankItemStock = $sql->setData('blank_item_stock', null, "id", SystemUtil::getUniqId($table, false, true));
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_code", $item_code);
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_sub_code", $color_code);
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "item_size_code", $size_code);
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "stock", $stock_num);
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "expected_import_date", $expected_import_date);
                            $blankItemStock = $sql->setData('blank_item_stock', $blankItemStock, "vendor", $data_vendor);
                            insert_or_update($plain_index, $blankItemStock, $insert_plain_item_stock_query, $insert_plain_item_stock);
                            if (in_array($item_code, constants::FAKE_ITEM)) {
                                $blankItemStock['id'] = SystemUtil::getUniqId('blank_item_stock', false, true);
                                $blankItemStock['item_code'] = constants::FAKE_ITEM_STOCK[$item_code];
                                insert_or_update($plain_index, $blankItemStock, $insert_plain_item_stock_query, $insert_plain_item_stock);
                            }
                        }
                    }
                }

                fakeStock();
                fakeStock('IT368', '"ITSI5392","ITSI5391","ITSI5389","ITSI5390","ITSI5394","ITSI5395","ITSI5388"');
                insert_or_update($stock_index, [], $insert_item_stock_query, $insert_item_stock, true);
                insert_or_update($plain_index, [], $insert_plain_item_stock_query, $insert_plain_item_stock, true);
            }

            if($error_text) $error_text = "（".rtrim($error_text, "、")."）";

            $message = $data_count."件中／".$error_count."件のエラー".$error_text."と".$insert_count."件の追加を行いました。";

            Globals::setSession("import_message", $message);

            fclose($handle);
            unlink($import_file);
            self::updateBlankItemStockNumber($master_item_type_data);
            return;
        }

        Globals::setSession("import_message", "CSVファイルをアップロードしてください。2".$import_file.$_FILES['csv']['error']);
    }

	static function import_blank_item_price()
	{
		global $sql;

		if(!CheckUtil::is_file_error("csv"))
		{
			Globals::setSession("import_message", "アップロードサイズを小さくしてください。");
			return;
		}

		$import_file = SystemUtil::doFileUpload(Globals::files("csv"));
		if(is_file($import_file))
		{
			$extension = pathinfo($import_file, PATHINFO_EXTENSION);
			if($extension != "csv")
			{
				Globals::setSession("import_message", "CSVファイルをアップロードしてください。1");
				unlink($import_file);
				return;
			}

			$data_count = 0;
			$error_text = "";

			//全削除
			$table = "master_blank_item_price";

			//インポート処理
			if(($handle = fopen($import_file, "r")) !== false)
			{
				$count = 0;
				while(($data = fgetcsv_reg($handle, 8000, ",")) !== false)
				{
					$count++;
					mb_convert_variables(mb_internal_encoding(), "SJIS-win", $data);

					//１行目スキップ
					if($count == 1)
					{
						if(Globals::post("one")) continue;
					}

					if(!is_array($data)) continue;

					$data_count++;
					$item_type = trim($data[0]);
					$item_type_sub = trim($data[2]);
					$item_type_size = trim($data[3]);
					$price = trim($data[1]);

					$where = $sql->setWhere($table, null, "item_type", "=", $item_type);
					$where = $sql->setWhere($table, $where, "item_type_sub", "=", $item_type_sub);
					$where = $sql->setWhere($table, $where, "item_type_size", "=", $item_type_size);

					$item = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

					if(!is_null($item)) {
						if($item['price'] == $price) {
							continue;
						} else {
							$update = $sql->setData($table, null, "price", $price);
							$sql->updateRecord($table, $update, $item['id']);
						}
					} else {
						$is_rec = $sql->setData($table, null, "id", SystemUtil::getUniqId($table, false, true));
						$is_rec = $sql->setData($table, $is_rec, "item_type", $item_type);
						$is_rec = $sql->setData($table, $is_rec, "price", $price);
						$is_rec = $sql->setData($table, $is_rec, "item_type_sub", $item_type_sub);
						$is_rec = $sql->setData($table, $is_rec, "item_type_size", $item_type_size);

						$sql->addRecord($table, $is_rec);
					}
				}
			}

			if($error_text) $error_text = "（".rtrim($error_text, "、")."）";

			$message = "価格は成功にインポートされました。";

			Globals::setSession("import_message", $message);

			fclose($handle);
			unlink($import_file);
			return;
		}
		Globals::setSession("import_message", "CSVファイルをアップロードしてください。2".$import_file.$_FILES['csv']['error']);
	}

	static function saveMemo()
	{
		global $sql;

		$data = array();

		$table = "pay";

		if(!$id = Globals::get("id")){ $data["state"] = 0; echo json_encode($data); exit; }
		if(!$p_rec = $sql->selectRecord($table, $id)){ $data["state"] = 0; echo json_encode($data); exit; }

		$update = $sql->setData($table, null, "memo", Globals::get("memo"));
		$sql->updateRecord($table, $update, $p_rec["id"]);

		$data["state"] = 1;
		echo json_encode($data); exit;
	}

    static function updateMailStatus()
	{
		global $sql;

        $table = "pay";
        $is_subcribe = 1;
        $id = Globals::get("id");
		$statuses = explode(',', Globals::get("statuses"));

		if (empty($id) || empty($statuses)) {
            jsonEncode(true);
        }

        if (in_array(10, $statuses)) {
            $is_subcribe = $status = 0;
        } elseif (in_array(1, $statuses)) {
            $status = 1;
        } elseif (in_array(0, $statuses)) {
            $status = 0;
        } else {
		    $status = array_sum($statuses);
        }

		$update = $sql->setData($table, null, "mail_status", (int)$status);
		$sql->updateRecord($table, $update, $id);

		$user_table = 'user';
        $update = $sql->setData($user_table, null, "is_subcribe", (int)$is_subcribe);
        $sql->updateRecord($user_table, $update, Globals::get("user"));

        jsonEncode(true);
	}


	static function pictProc()
	{
		function complete($zipfile)
		{
echo <<< EOM
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
  <strong>一括画像圧縮ファイル：</strong><br>
  <a href="{$zipfile}">http://up-t.jp/{$zipfile}</a><br>

  <em>ファイル名命名規約</em>

  <pre>
  1610030032_p011.png

    1610030032 .. 注文番号
    p .. p: プレビュー画像, i: 印刷用
    01 .. (01～99): 注文番号内デザイン識別用
    1  .. 1: 表, 2: 裏, 3: 左, 4: 右
  </pre>
</body>
</html>
EOM;
		}

		$debug_mode = false;

		set_time_limit(600);

		global $sql;

		$choose_type = Globals::get("type");
		$list = Globals::get("list");
		$list = explode("/", $list);

		$img_list = array();
        $count_list = count($list);
		for($i = 0; $i < $count_list; $i++)
		{
			$id = $list[$i];

			if($rec = $sql->selectRecord("pay", $id))
			{
				$pay_num = $rec["pay_num"];
				$img_list[$pay_num] = getImageList($id);
			}
		}

		$zip = new ZipArchive();
		$dir = 'pict_archives_tmp';

		FileUtil::mkdirAndClearFile($dir);

		$zipfile = $dir .'/'. 'pict_archives.zip';
		$res = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		if ($res === true)
		{
			foreach($img_list as $order => $items)
			{
				$item_idx = 0;
				foreach($items as $item => $imgs)
				{
				    $count_imgs = count($imgs);
					for($i = 0; $i < $count_imgs; $i++)
					{
						if($i == 0) continue;
						if($choose_type == "1" && ($i >= 1 && $i <= 4)) continue;
						if($choose_type == "0" && ($i >= 5 && $i <= 8)) continue;

						$url = $imgs[$i];
						if (!empty($url))
						{
							// Does not generate an error, even 404
							$context = stream_context_create(array(
									'http' => array('ignore_errors' => true)
							));

							$fext = ".png";
							$data = file_get_contents($url, false, $context);
							if (!strpos($http_response_header[0], '200')) {
								// Error handling
								// 1610160044, 1610160015, 1610020040, 1610020023
								$data = $url;
								$fext = ".txt";
							}

							$type = "p";
							if($i > 4) $type = "i";

							$item_seq = $type . sprintf("%02s",	$item_idx + 1);
							$side_idx = $i;
							if($side_idx > 4) $side_idx -= 4;

							$fname = $dir ."/". $order ."_". $item_seq. $side_idx .$fext;
							file_put_contents($fname, $data);
						}
					}
					$item_idx++;
				}
			}
		}

		// $options = array('add_path' => "pict_archives_tmp", 'remove_all_path' => TRUE);
		$options = array('remove_all_path' => TRUE);
		$zip->addGlob($dir . '/*.{png,txt}', GLOB_BRACE, $options);
		$zip->close();

		// zip アーカイブでタイムアウト(504)になってしまうため、先に表示する
		if ($debug_mode)
		{
			echo "<pre style='text-align:left;'>";
			var_dump($img_list);
			echo "</pre>";
		}
		else
		{
			// header('Content-Type: application/octet-stream');
			// header(sprintf('Content-Disposition: attachment; filename="%s"', basename($zipfile)) );
			// header(sprintf('Content-Length: %d', filesize($zipfile)));
			// readfile($zipfile);

			complete($zipfile);
		}
	}

	static function merge2order(){
		global $sql;
		$data = array();

		$order1 = Globals::get("order1");
		$order2 = Globals::get("order2");
		$table = 'pay';

		$order1Data = $sql->queryRaw('pay', "SELECT * FROM pay WHERE id = '$order1'");
		$order2Data = $sql->queryRaw('pay', "SELECT * FROM pay WHERE id = '$order2'");

		$newOrder = false;
		if($order1Data && $order2Data)
		{
			$payIdOrder1 = false;
			foreach ($order1Data as $key => $val) {
				$newOrder = $val;
				$payIdOrder1 = $val["id"];
			}

			$newOrder["id"] = SystemUtil::getUniqId($table, false, true);
			$newOrder["pay_num"] = getPayNum();
			$newOrder["date_y"] = date("Y");
			$newOrder["date_m"] = date("m");
			$newOrder["date_d"] = date("d");
			$newOrder["conf_datetime"] = date("Y-m-d H:i:s");
			$newOrder["regist_unix"] = time();
			$newOrder["check_merge"] = "1";

			foreach ($order2Data as $key => $val) {
				if(
					$newOrder["mail"] == $val["mail"] &&
					$newOrder["pay_type"] == $val["pay_type"]&&
					($newOrder['pay_type'] == "cod" || $newOrder["pay_type"] == "after2")
				)
				{
					if(
						(
							($newOrder['pay_type'] == 'cod' && $newOrder['delivery_state'] != 2) ||
							($newOrder['pay_type'] == 'after2' && $newOrder['pay_state'] != 2)
						) &&
						(
							($val['pay_type'] == 'cod' && $val['delivery_state'] != 2) ||
							($val['pay_type'] == 'after2' && $val['pay_state'] != 2)
						)
					)
					{
                        $newOrder["pay_rank"] += (int)$val["pay_rank"];
						$newOrder["pay_price"] += (int)$val["pay_price"];
						$newOrder["pay_point"] += (int)$val["pay_point"];
						$newOrder["pay_discount"] += (int)$val["pay_discount"];
						$newOrder["pay_tax"] = ((int)$newOrder['pay_price'] + (int)$newOrder['pay_cod'] + (int)$newOrder['deferred_payment'] - (int)$newOrder['pay_point'] - (int)$newOrder["pay_rank"]) * getTaxRate($val["regist_unix"]);
						$newOrder["pay_total"] = ((int)$newOrder["pay_price"] + (int)$newOrder["pay_cod"] + (int)$newOrder['deferred_payment'] + (int)$newOrder['pay_tax']) - (int)$newOrder['pay_point'] - (int)$newOrder["pay_rank"];
						if($payIdOrder1) {
							mergePayItem($payIdOrder1, $val["id"], $newOrder['id']);
						}
						unset($newOrder['printty_export_datetime']);
						$sql->addRecord($table, $newOrder);
						registCompMerge2order($table, $newOrder);

						if($newOrder['pay_type'] == $val['pay_type'] && $newOrder['pay_type'] == 'after2') {
							changePayPay($val['id'], 2);
							changePayPay($payIdOrder1, 2);
						}
						changePayDelivery($val['id'], 2, null, 'merge2order');
						changePayDelivery($payIdOrder1, 2, null, 'merge2order');
						$data['msg'] = 'Merge successfully!';
					}
					else
					{
						$data['msg'] = 'Order was canceled';
					}
				}
				else
				{
					$data['msg'] = 'Mail are not same or pay type is not cod, after2 or pay type are not same';
				}
			}
		}
		else
		{
			$data['msg'] = 'Order Id not found!';
		}
		jsonEncode($data);
	}

    static function merge2orderPasral(){
        global $sql;
        $data = array();

        $order1 = Globals::get("order1");
        $order2 = Globals::get("order2");
        $table = 'pay';

        $order1Data = $sql->queryRaw('pay', "SELECT * FROM pay WHERE id = '$order1'");
        $order2Data = $sql->queryRaw('pay', "SELECT * FROM pay WHERE id = '$order2'");

        $newOrder = false;
        if($order1Data && $order2Data)
        {
            $payIdOrder1 = false;
            foreach ($order1Data as $key => $val) {
                $newOrder = $val;
                $payIdOrder1 = $val["id"];
            }

            $newOrder["id"] = SystemUtil::getUniqId($table, false, true);
            $newOrder["pay_num"] = getPayNum();
            $newOrder["date_y"] = date("Y");
            $newOrder["date_m"] = date("m");
            $newOrder["date_d"] = date("d");
            $newOrder["conf_datetime"] = date("Y-m-d H:i:s");
            $newOrder["regist_unix"] = time();
            $newOrder["check_merge"] = "1";

            foreach ($order2Data as $key => $val) {
                if(
                    $newOrder["mail"] == $val["mail"] &&
                    $newOrder["pay_type"] == $val["pay_type"]&&
                    ($newOrder['pay_type'] == "cod" || $newOrder["pay_type"] == "after2")
                )
                {
                    if(
                        (
                            ($newOrder['pay_type'] == 'cod' && $newOrder['delivery_state'] != 2) ||
                            ($newOrder['pay_type'] == 'after2' && $newOrder['pay_state'] != 2)
                        ) &&
                        (
                            ($val['pay_type'] == 'cod' && $val['delivery_state'] != 2) ||
                            ($val['pay_type'] == 'after2' && $val['pay_state'] != 2)
                        )
                    )
                    {
                        $newOrder["pay_rank"] += (int)$val["pay_rank"];
                        $newOrder["pay_price"] += (int)$val["pay_price"];
                        $newOrder["pay_point"] += (int)$val["pay_point"];
                        $newOrder["pay_discount"] += (int)$val["pay_discount"];
                        $newOrder["pay_tax"] = ((int)$newOrder['pay_price'] + (int)$newOrder['pay_cod'] + (int)$newOrder['deferred_payment'] - (int)$newOrder['pay_point'] - (int)$newOrder["pay_rank"]) * getTaxRate($val["regist_unix"]);
                        $newOrder["pay_total"] = ((int)$newOrder["pay_price"] + (int)$newOrder["pay_cod"] + (int)$newOrder['deferred_payment'] + (int)$newOrder['pay_tax']) - (int)$newOrder['pay_point'] - (int)$newOrder["pay_rank"];
                        if($payIdOrder1) {
                            mergePayItem($payIdOrder1, $val["id"], $newOrder['id']);
                        }
                        unset($newOrder['printty_export_datetime']);
                        $sql->addRecord($table, $newOrder);
                        registCompMerge2order($table, $newOrder);

                        if($newOrder['pay_type'] == $val['pay_type'] && $newOrder['pay_type'] == 'after2') {
                            changePayPay($val['id'], 2);
                            changePayPay($payIdOrder1, 2);
                        }
                        changePayDelivery($val['id'], 2, null, 'merge2order');
                        changePayDelivery($payIdOrder1, 2, null, 'merge2order');
                        $data['msg'] = 'Merge successfully!';
                    }
                    else
                    {
                        $data['msg'] = 'Order was canceled';
                    }
                }
                else
                {
                    $data['msg'] = 'Mail are not same or pay type is not cod, after2 or pay type are not same';
                }
            }
        }
        else
        {
            $data['msg'] = 'Order Id not found!';
        }
        jsonEncode($data);
    }

	static  function getUniqueId(){
		if($table = Globals::get("type")) {
			if($id = SystemUtil::getUniqId($table, false, true)) {
				$data['id'] = $id;
				jsonEncode($data);
			}
		}
	}

    static  function getUniqueIdPasral(){
        if($table = Globals::get("type")) {
            if($id = SystemUtil::getUniqId($table, false, true)) {
                $data['id'] = $id;
                jsonEncode($data);
            }
        }
    }

	static function deleteStore() {
		global $sql;

		$id = Globals::get('id');
		$table = 'store_info';
        if (Globals::get('type') == "personal_shop_info") {
            $table = "personal_shop_info";
        }
		$sql->deleteRecord($table, $id);

        // check to redirect to base
        if (Globals::get('type') == "personal_shop_info") {
            $list_shop = getListShop();
            if ($list_shop) {
                HttpUtil::location(sprintf('%s?is_admin=true&session=%s&m_type=%s', $list_shop[0]['url'], Globals::session('DRAW_TOOL_SESSION'), 'store_info'));
            } else {
                HttpUtil::location("/page.php?p=dashboard");
            }
        } else {
            HttpUtil::location("/page.php?p=store_info");
        }
	}

	static function getStoreById(){
		global $sql;

		$id = Globals::get('id');
		$table = 'store_info';
		$data = array();

		$store = $sql->selectRecord($table, $id);

		jsonEncode($store);
	}

    /**
     * Download shipped orders
     */
    static function downloadCsvShipment()
    {
        global $sql;
        $date     = Globals::get("date");
        $filename = Globals::get("name") . "_" . date("Y_m_d") . ".csv";

        if (Globals::get("type") == 'month') {
			$date_y       = date('Y', strtotime($date));
			$date_m       = date('m', strtotime($date));
			$date_d       = date('d', strtotime($date));
			$n_month      = date("n");
			$current_year = date("Y");
			$last_day     = date('t', strtotime(sprintf('%s-%s', $date_y, $date_m)));
			$contents     = '発送日付' . "," . '注文日日付' . "," . '注文番号' . "," . '名前' . "," . '決済方法' . "," . '金額' . "," . '販売サイト' . "\n";


			if ($date_y == $current_year && $date_m > $n_month || $date_y == $current_year && $date_m == $n_month && $date_d > $last_day) {
				// Do nothing
			} else {
				$tmp["date_y"] = $date_y;
				$tmp["date_m"] = $date_m < 10 ? "0{$date_m}" : $date_m;
				$tmp["date_d"] = $date_d < 10 ? "0{$date_d}" : $date_d;
				$end_date      = sprintf('%s-%s-%s 23:59:59', $date_y, $date_m, $last_day);
				$start_date    = sprintf('%s-%s-%s 00:00:00', $date_y, $date_m, "01");

				$table = "pay";
				$where = $sql->setWhere($table, null, "send_datetime", ">=", $start_date);
				$where = $sql->setWhere($table, $where, "send_datetime", "<=", $end_date);
				$where = $sql->setWhere($table, $where, "delivery_state", "=", 1, "AND", "(");

				$sub_table  = "after_log";
				$sub_where  = $sql->setWhere($sub_table, null, "state", "IN", [4]);
				$subQueries = [
					'table'  => $sub_table,
					'select' => 'pay_id',
					'where'  => $sub_where,
				];
				$where      = $sql->setWhere($table, $where, "id", "SUB", $subQueries, "OR", ")");

				$result = $sql->getSelectResult($table, $where);

				$payment_types = [
					'card'          => 'クレジットカード決済',
					'after2'        => 'コンビニ後払い',
					'conveni'       => 'コンビニ決済',
					'bank'          => '銀行振込',
					'cod'           => '代金引換',
					'ponpare'       => 'ポンパレ購入済み',
					'mobile_client' => 'モバイルアプリ',
					'amazon_pay'    => 'AmazonPay',
				];

				while ($pay = $sql->sql_fetch_assoc($result)) {
					$contents .= '"' . date('Y年m月d日', strtotime($pay['send_datetime'])) . '"' . "," . '"' .
						date('Y年m月d日', $pay['regist_unix']) . '"' .
						"," . '"' . $pay["pay_num"] . '"' . "," . '"' . $pay['name'] .
						'"' . "," . '"' . $payment_types[$pay['pay_type']] . '"' . "," . '"' . number_format($pay['pay_total']) . '"' . "," . 'ondemand';
					$contents .= "\n";
				}
			}

        } else {
            $date_y       = date('Y', strtotime($date));
            $date_m       = date('m', strtotime($date));
            $date_d       = date('d', strtotime($date));
            $n_month      = date("n");
            $current_year = date("Y");
            $last_day     = date('t', strtotime(sprintf('%s-%s', $date_y, $date_m)));
			$contents     = '発送日付' . "," . '注文日日付' . "," . '注文番号' . "," . '名前' . "," . '決済方法' . "," . '金額' . "," . '販売サイト' . "\n";


            if ($date_y == $current_year && $date_m > $n_month || $date_y == $current_year && $date_m == $n_month && $date_d > $last_day) {
                // Do nothing
            } else {
                $tmp["date_y"] = $date_y;
                $tmp["date_m"] = $date_m < 10 ? "0{$date_m}" : $date_m;
                $tmp["date_d"] = $date_d < 10 ? "0{$date_d}" : $date_d;
                $end_date      = sprintf('%s-%s-%s 23:59:59', $date_y, $date_m, $date_d);
                $start_date    = sprintf('%s-%s-%s 00:00:00', $date_y, $date_m, $date_d);

                $table = "pay";
                $where = $sql->setWhere($table, null, "send_datetime", ">=", $start_date);
                $where = $sql->setWhere($table, $where, "send_datetime", "<=", $end_date);
                $where = $sql->setWhere($table, $where, "delivery_state", "=", 1, "AND", "(");

                $sub_table  = "after_log";
                $sub_where  = $sql->setWhere($sub_table, null, "state", "IN", [4]);
                $subQueries = [
                    'table'  => $sub_table,
                    'select' => 'pay_id',
                    'where'  => $sub_where,
                ];
                $where      = $sql->setWhere($table, $where, "id", "SUB", $subQueries, "OR", ")");

                $result = $sql->getSelectResult($table, $where);

                $payment_types = [
                    'card'          => 'クレジットカード決済',
                    'after2'        => 'コンビニ後払い',
                    'conveni'       => 'コンビニ決済',
                    'bank'          => '銀行振込',
                    'cod'           => '代金引換',
                    'ponpare'       => 'ポンパレ購入済み',
                    'mobile_client' => 'モバイルアプリ',
                    'amazon_pay'    => 'AmazonPay',
                ];

                while ($pay = $sql->sql_fetch_assoc($result)) {
                    $contents .= '"' . date('Y年m月d日', strtotime($pay['send_datetime'])) . '"' . "," . '"' .
                        date('Y年m月d日', $pay['regist_unix']) . '"' .
                        "," . '"' . $pay["pay_num"] . '"' . "," . '"' . $pay['name'] .
						'"' . "," . '"' . $payment_types[$pay['pay_type']] . '"' . "," . '"' . number_format($pay['pay_total']) . '"' . "," . 'ondemand';
                    $contents .= "\n";
                }
            }
        }

        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $contents;

        ob_end_flush();
        exit;
        //HttpUtil::download($filename, $contents);
    }

	/**
	 * add tag, category
	 * @param $cate_p
	 * @param $cate_c
	 * @param $cate_gc
	 * @param $updateCategory
	 * @param $itemId
	 */
    static function settingTagAndCategory($cate_p, $cate_c, $cate_gc, $updateCategory, $itemId)
    {
        global $sql;
        //update category
        if (!empty($cate_p) || !empty($cate_c) || !empty($cate_gc)) {
            $sql->updateRecord('item', $updateCategory, $itemId);
        }
    }

	/**
	 * add, update tag
	 * @param $itemId
	 * @param $tag
	 */
	static function addMasterItemTag($itemId, $tag) {
    	addMasterItemTag($itemId, $tag);
	}

    /**
     * remove tag, category
     * @param $cate_p
     * @param $cate_c
     * @param $cate_gc
     * @param $itemId
     */
    static function removeTagAndCategory($cate_p, $cate_c, $cate_gc, $itemId)
    {
        global $sql;
        //remove category
        $itemDetail = $sql->selectRecord('item', $itemId);
        if (!empty($cate_p) || !empty($cate_c) || !empty($cate_gc)) {
            if ($itemDetail['category_1'] == $cate_p && $itemDetail['category_2'] == $cate_c && $itemDetail['category_3'] == $cate_gc) {
                $sql->updateRecord('item', array('category_1' => null, 'category_2' => null, 'category_3' => null), $itemId);
            }
        }
    }

	/**
	 * add or remove category
	 */
	static function handleCategoryAndTag() {
		$data = [];
		$cate_p = Globals::get('category_1');
		$cate_c = Globals::get('category_2');
		$cate_gc = Globals::get('category_3');
		$itemIdString = Globals::get('item_id');
		$itemIds = $itemIdString != '' ? explode('/', $itemIdString) : null;

		if((empty($cate_p) && empty($cate_c) && empty($cate_gc ))) {
			jsonEncode($data['msg'] = 'カテゴリを選んでください');
		} else {
			$updateCategory['category_1'] = $cate_p;
			$updateCategory['category_2'] = $cate_c;
			$updateCategory['category_3'] = $cate_gc;

			if(!is_null($itemIds)) {
				foreach ($itemIds as $itemId) {
					if(Globals::get('type') == 'setting') {
						Process::settingTagAndCategory($cate_p, $cate_c, $cate_gc, $updateCategory, $itemId);
					} else if(Globals::get('type') == 'cancel') {
						Process::removeTagAndCategory($cate_p, $cate_c, $cate_gc, $itemId);
					}
				}

				jsonEncode($data['msg'] = '成功');
			} else {
				jsonEncode($data['msg'] = '複数の商品を選択');
			}
		}
	}

    static function changeMasterIsMain()
    {
        $data = array();
        if(!$id = Globals::get("id")) jsonEncode($data);
        if(!$table = Globals::get("type")) jsonEncode($data);

        changeMasterIsMain($table, $id);
        jsonEncode($data);
    }

    public static function updatePolicyStatus()
    {
        global $sql;
        $id              = Globals::get("id");
        $table           = "pay";
        if(Globals::get("policy_check")){
            $policy_check = Globals::get("policy_check");
            if ($rec = $sql->selectRecord($table, $id)) {
                $update = $sql->setData($table, null, 'policy_check', $policy_check);
                $sql->updateRecord($table, $update, $id);
            }

            HttpUtil::location($_SERVER['HTTP_REFERER']);
        }
        else{
            $policy_check    = Globals::post("policy_check");
            $policy_statuses = Globals::$statuses;

            if (!isset($policy_statuses[$policy_check])) {
                $policy_check = 0;
            }

            if ($rec = $sql->selectRecord($table, $id)) {
                $update = $sql->setData($table, null, 'policy_check', $policy_check);
                $sql->updateRecord($table, $update, $id);
            }

            HttpUtil::location($_SERVER['HTTP_REFERER']);
        }

    }

    public static function updatePendingStatus()
    {
        global $sql;
        $id              = Globals::get("id");
        $table           = "pay";
        if(Globals::get("pending")){
            $pending = Globals::get("pending");
            if ($rec = $sql->selectRecord($table, $id)) {
                $update = $sql->setData($table, null, 'pending', $pending);
                $sql->updateRecord($table, $update, $id);
            }

            HttpUtil::location($_SERVER['HTTP_REFERER']);
        }
        else{
            $pending    = Globals::post("pending");
            $pending_statuses = Globals::$pending;

            if (!isset($pending_statuses[$pending])) {
                $pending = 0;
            }

            if ($rec = $sql->selectRecord($table, $id)) {
                $update = $sql->setData($table, null, 'pending', $pending);
                $sql->updateRecord($table, $update, $id);
            }

            HttpUtil::location($_SERVER['HTTP_REFERER']);
        }

    }

    static function changeMultiPolicyCheck()
    {
        $state = 0;
        $ids = Globals::get("list");

        if(empty($ids)) {
            jsonEncode(['state' => $state]);
        }

        try {
            global $sql;

            $sql->rawQuery(sprintf('UPDATE pay SET policy_check = %s WHERE id IN (%s)',
                                   array_flip(Globals::$statuses)[Globals::$statuses[3]], $ids));

            $state = 1;
        } catch (Exception $exception) {
            // The empty message
        }

        jsonEncode(['state' => $state]);
    }
    static function searchMessage(){
        global $sql;

        $id = Globals::get('id');

        $table = 'master_item_note';
        $data = array();

        $message = $sql->selectRecord($table, $id);

        jsonEncode($message);
    }

    static function changeStateTemplate()
    {
        global $sql;

        if (Globals::get('state') == 1) {
            $update = $sql->setData('mail_template', null, "state", 0);
            $sql->updateRecord('mail_template', $update, Globals::get('template_id'));
        }

        if (Globals::get('state') == 0) {
            $update = $sql->setData('mail_template', null, "state", 1);
            $sql->updateRecord('mail_template', $update, Globals::get('template_id'));
        }

    }
    //change Nobori_stt
    static function changeNoboriStt()
    {
        global $sql;
        $id = Globals::get("id");
        $state = Globals::get("state");

        $update = $sql->setData('pay', null, "nobori_stt", $state);
        $sql->updateRecord('pay', $update,$id);
        jsonEncode(true);
    }
    static function changeMultiNoboriStt()
    {
        global $sql;
        $data = array();
        $data["state"] = -1;

        if(!$list = Globals::get("list")) jsonEncode($data);
        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        $list = explode("/", $list);
        $count_list = count($list);
        for($i = 0; $i < $count_list; $i++)
        {
            $pay_rec = $sql->selectRecord("pay", $list[$i]);
            if($pay_rec && $pay_rec["nobori_flg"]==1 && $pay_rec["nobori_stt"]!= $state){
                $update = $sql->setData('pay', null, "nobori_stt", $state);
                $sql->updateRecord('pay', $update,$list[$i]);
            }
        }
        $data["state"] = $state + 0;
        jsonEncode($data);
    }

    static function updateDeliveryService()
    {
        global $sql;

        $id = Globals::get("id");
        $delivery_service = Globals::post("delivery_service");

        if (!empty($delivery_service)) {
            $update = $sql->setData('pay', null, "delivery_service", $delivery_service);
            $sql->updateRecord('pay', $update,$id);
        }

        HttpUtil::location($_SERVER['HTTP_REFERER']);
    }

    static function updateTrackingNumber()
    {
        global $sql;

        $id = Globals::get("id");
        $tracking_number = Globals::post("tracking_number");

        if (!empty($tracking_number)) {
            $update = $sql->setData('pay', null, "tracking_number", $tracking_number);
            $sql->updateRecord('pay', $update,$id);
        }

        HttpUtil::location($_SERVER['HTTP_REFERER']);
    }

    static function deleteCreditCard() {
        global $sql;

        $id = Globals::get('id');
        $member_id = Globals::get('gmo_member_id');
        $card_seq= Globals::get('card_seq');
        $table = 'card_information';

        $sql->deleteRecord($table, $id);
        gmoFunc::deleteCard($member_id,$card_seq);
        HttpUtil::location("/search.php?type=card_information");
    }

    static function changePrinttyExport()
    {
        global $sql;
        $data = array();
        $data["state"] = -1;

        if(!$list = Globals::get("list")) jsonEncode($data);
        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        $list = explode("/", $list);
        $count_list = count($list);
        for($i = 0; $i < $count_list; $i++)
        {
            $pay_rec = $sql->selectRecord("pay", $list[$i]);
            if(!empty($pay_rec)){
                $update = $sql->setData('pay', null, "printty_export", '');
                $update = $sql->setData('pay', $update, "printty_export_datetime", null);
                $sql->updateRecord('pay', $update,$list[$i]);
            }
        }
        $data["state"] = $state + 0;
        jsonEncode($data);
    }

    static function changePaySend_delivery_slip()
    {
        global $sql;
        $data = array();
        $data["state"] = -1;

        if(!$id = Globals::get("id")) jsonEncode($data);
        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);


        $table = "pay";
        if(!$rec = $sql->selectRecord($table, $id)) return;

        if($rec["send_delivery_slip"] != $state)
        {
            $update = $sql->setData($table, null, "send_delivery_slip", $state);
            $update = $sql->setData($table, $update, "card_thank", $state);
            $sql->updateRecord($table, $update, $rec["id"]);
        }

        $data["state"] = $state + 0;
        jsonEncode($data);
    }


    static function getAddressUser()
    {
        $data = getAddressUser();

       return jsonEncode($data);
    }

    static function searchContactShop(){
        return self::getAddressUser();
    }

    static function getPayItemById()
    {
        global $sql;
        $id = Globals::get('id');
        $table = "pay_item";
        $data = array();

        $where = $sql->setWhere($table, null, "pay", "=", $id);
        $result = $sql->getSelectResult($table, $where);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $data[] = array($rec["item"],$rec["item_price"],$rec["item_row"]);
        }
        jsonEncode($data);
    }

    static function checkRefreshToken()
    {
        global $sql;
        $data = array();

        $userId = Globals::session("LOGIN_ID");
        if(!empty($userId)){
            $user = $sql->selectRecord('user', $userId);
            if(!empty($user["refresh_token_base"])){
                $Date = date('m/d/Y', $user["created_at_token"]);
                $dateCheck = date('Y-m-d', strtotime($Date. ' + 29 days'));
                $datecurrent = date('Y-m-d');

                if($datecurrent < $dateCheck){
                    $data["token"] = true;
                }
                else{
                    $data["token"] = 'expired';
                }
            }
            else
            {
                $data["token"] = false;
            }
        }
        jsonEncode($data);
    }

    static function updateBaseOrder()
    {
        global $sql;
        $data = array();

        $urlref = $_SERVER["HTTP_REFERER"];

        $parts = parse_url($urlref);
        parse_str($parts['query'], $query);
        if(!empty($query['code']) && !empty($query['state'])){
            $tockenbase = getTockenBase($query['code'],$query['state']);
            $refreshTokenBase = $tockenbase;
        }
        else{
            $refreshTokenBase = getRefreshTockenBase($getOrder=1);
        }
        if(!empty($refreshTokenBase) && !empty(Globals::session("LOGIN_ID"))){

            getShopBaseInfo($refreshTokenBase,$updated = 1);

            $headers = array(
                'Authorization: Bearer ' .$refreshTokenBase,
            );
            $url = "https://api.thebase.in/1/orders?limit=100";
//            $postdata["end_ordered"]= date('Y-m-d H:i:s');
//            $postdata["start_ordered"]= date('Y-m-d H:i:s');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            $myArray = json_decode($result, true);

            $userInfo = SystemUtil::getMyProfile();
            foreach ($myArray["orders"] as $orders) {
                $urldetail = "https://api.thebase.in/1/orders/detail/".$orders["unique_key"];
                $ch = curl_init($urldetail);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $resultdetail = curl_exec($ch);
                curl_close($ch);
                $ordersdetail = json_decode($resultdetail, true);

                $query_get_item = "SELECT * FROM base_orders WHERE base_order_id = '".$ordersdetail["order"]["unique_key"]."'";
                $resultFind = $sql->rawQuery($query_get_item) ;
                if($resultFind->num_rows == 0){
                    $al_table = 'base_orders';


                    $al_rec = $sql->setData($al_table, null, "base_order_id", $ordersdetail["order"]["unique_key"]);

                    if(!empty($ordersdetail["order"]["order_receiver"]["first_name"]) && !empty($ordersdetail["order"]["order_receiver"]["first_name"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "name", $ordersdetail["order"]["order_receiver"]["last_name"].$ordersdetail["order"]["order_receiver"]["first_name"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "name", $ordersdetail["order"]["last_name"].$ordersdetail["order"]["first_name"]);
                    }

                    if(!empty($ordersdetail["order"]["order_receiver"]["zip_code"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "add_num", $ordersdetail["order"]["order_receiver"]["zip_code"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "add_num", $ordersdetail["order"]["zip_code"]);
                    }

                    if(!empty($ordersdetail["order"]["order_receiver"]["prefecture"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "add_pre", $ordersdetail["order"]["order_receiver"]["prefecture"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "add_pre", $ordersdetail["order"]["prefecture"]);
                    }

                    if(!empty($ordersdetail["order"]["order_receiver"]["address"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "add_sub", $ordersdetail["order"]["order_receiver"]["address"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "add_sub", $ordersdetail["order"]["address"]);
                    }

                    if(!empty($ordersdetail["order"]["order_receiver"]["address2"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "add_sub2", $ordersdetail["order"]["order_receiver"]["address2"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "add_sub2", $ordersdetail["order"]["address2"]);
                    }

                    if(!empty($ordersdetail["order"]["order_receiver"]["tel"])){
                        $al_rec = $sql->setData($al_table, $al_rec, "tel", $ordersdetail["order"]["order_receiver"]["tel"]);
                    }
                    else{
                        $al_rec = $sql->setData($al_table, $al_rec, "tel", $ordersdetail["order"]["tel"]);
                    }


                    $al_rec = $sql->setData($al_table, $al_rec, "ordered", $ordersdetail["order"]["ordered"]);

                    $al_rec = $sql->setData($al_table, $al_rec, "base_shop_id", $userInfo["base_shop_id"]);
                    $check_order = 0;
                    foreach ($ordersdetail["order"]["order_items"] as $ordersDetailcheck) {
                        $query_get_item_check = "SELECT * FROM item WHERE item_base_id = '".$ordersDetailcheck["item_id"]."'";
                        $result_check = $sql->sql_fetch_assoc($sql->rawQuery($query_get_item_check)) ;
                        if(!empty($result_check["id"])){
                            $check_order = 1;
                            break;
                        }
                    }
                    if($check_order == 1){
                        $sql->addRecord($al_table, $al_rec);
                        foreach ($ordersdetail["order"]["order_items"] as $ordersDetail) {
                            $query_get_item = "SELECT * FROM item WHERE item_base_id = '".$ordersDetail["item_id"]."'";
                            $result = $sql->sql_fetch_assoc($sql->rawQuery($query_get_item)) ;
                            $al_table = 'base_order_items';
                            $al_rec_item = $sql->setData($al_table, null, "base_order_id", $ordersdetail["order"]["unique_key"]);
                            $al_rec_item = $sql->setData($al_table, $al_rec_item, "amount", $ordersDetail["amount"]);
                            $al_rec_item = $sql->setData($al_table, $al_rec_item, "item", $result["id"]);
                            $al_rec_item = $sql->setData($al_table, $al_rec_item, "base_item_id", $ordersDetail["item_id"]);
                            if(!empty($result["id"])){
                                $sql->addRecord($al_table, $al_rec_item);
                            }
                        }
                    }
                }
            }
            $data["state"] = true;
        }
        else{
            $data["state"] = false;
        }
        jsonEncode($data);
    }

    static function webBaseOrder()
    {
        global $sql;
        if (!empty(Globals::get("BaseOrderId"))) {
            $query_base_get_item = "SELECT * FROM base_order_items WHERE base_order_id = '" . Globals::get("BaseOrderId") . "' AND item != '' AND item is not null ";
            $result = $sql->rawQuery($query_base_get_item);

            while ($orders_item = $sql->sql_fetch_assoc($result)) {
                $query_get_item = "SELECT * FROM item WHERE id = '" . $orders_item["item"] . "'";
                $item = $sql->sql_fetch_assoc($sql->rawQuery($query_get_item));

                Globals::setGet('id', $item["id"]);
                Globals::setGet('quantity', $orders_item["amount"]);
                Globals::setPost('multiple', true);
                Globals::setSession('orderBaseCart', true);
                self::web2web();
            }

            HttpUtil::location('page.php?p=cart&orderFromBase='.Globals::get("BaseOrderId"));
        }
    }


    static function loginLine()
    {
        $user_profile = null;
        $email = '';

        switch (Globals::get('state')) {
            case 'line-login':
                loginLine();
                break;
            case 'line-code':
                try {
                    $result = getLineAccessToken(Globals::get('code'));

                    $user_profile = getLineProfile($result['access_token']);

                    $email = getLineEmail(base64_decode($result['id_token']));
                } catch (Exception $exception) {
                    //
                }

                break;
            case 'line-access-token':

                break;
            default:
                loginLine();
                break;
        }

        global $sql;
        $ret = array("social_id" => "", "name" => "", "mail" => "");
        $social_type = 'Line';

        if(!empty($user_profile->userId)) {
            $ret["social_id"] = $user_profile->userId;
        }

        if(!empty($user_profile->displayName)) {
            $ret["name"] = $user_profile->displayName;
        }

        if(!empty($email)) {
            $ret["mail"] = $email;
        }

        if(!$ret["social_id"]) return;

        //アカウント確認
        $table = "user";
        $where = $sql->setWhere($table, null, "social_type", "=", $social_type);
        $where = $sql->setWhere($table, $where, "social_id", "=", $ret["social_id"]);
        $where = $sql->setWhere($table, $where, "state", "=", 1);
        $order = $sql->setOrder($table, null, "edit_unix", "DESC");
        if($rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1))))
        {
            //既存アカウント
            Globals::setSession("LOGIN_TYPE", "user");
            Globals::setSession("LOGIN_ID", $rec["id"]);

            $table = "user";
            $update = $sql->setData($table, null, "login_unix", time());
            $sql->updateRecord($table, $update, $rec["id"]);
            changeStatusLogin(null, $rec["id"]);

            HttpUtil::location("/");
        }
        else
        {
            //新規アカウント作成
            $ret["page"] = "check";
            $ret["TOKEN_CODE"] = SystemUtil::setTokenCode("regist", "user");
            Globals::setSession("SOCIAL_TYPE", $social_type);
            Globals::setSession("SOCIAL_ID", $ret["social_id"]);
            HttpUtil::postLocation("/regist.php?type=user", $ret);
        }
    }

    static function loginInstagram()
    {
        $user_profile = null;
        $email = '';

        switch (Globals::get('state')) {
            case 'insta-login':
                loginInstagram();
                break;
            case 'insta-code':
                try {
                    $result = getInstagramAccessToken(Globals::get('code'));

                    $user_profile = getInstagramProfile($result['access_token'], $result['user_id']);

                    $email = null;
                } catch (Exception $exception) {
                    //
                }

                break;
            case 'insta-access-token':

                break;
            default:
                loginInstagram();
                break;
        }

        global $sql;
        $ret = array("social_id" => "", "name" => "", "mail" => "");
        $social_type = 'Instagram';

        if(!empty($user_profile->id)) {
            $ret["social_id"] = $user_profile->id;
        }

        if(!empty($user_profile->username)) {
            $ret["name"] = $user_profile->username;
        }

        if(!empty($email)) {
            $ret["mail"] = $email;
        }

        if(!$ret["social_id"]) return;

        //アカウント確認
        $table = "user";
        $where = $sql->setWhere($table, null, "social_type", "=", $social_type);
        $where = $sql->setWhere($table, $where, "social_id", "=", $ret["social_id"]);
        $where = $sql->setWhere($table, $where, "state", "=", 1);
        $order = $sql->setOrder($table, null, "edit_unix", "DESC");
        if($rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1))))
        {
            //既存アカウント
            Globals::setSession("LOGIN_TYPE", "user");
            Globals::setSession("LOGIN_ID", $rec["id"]);

            $table = "user";
            $update = $sql->setData($table, null, "login_unix", time());
            $sql->updateRecord($table, $update, $rec["id"]);
            changeStatusLogin(null, $rec["id"]);

            HttpUtil::location("/");
        }
        else
        {
            //新規アカウント作成
            $ret["page"] = "check";
            $ret["TOKEN_CODE"] = SystemUtil::setTokenCode("regist", "user");
            Globals::setSession("SOCIAL_TYPE", $social_type);
            Globals::setSession("SOCIAL_ID", $ret["social_id"]);
            HttpUtil::postLocation("/regist.php?type=user", $ret);
        }
    }

    static function displayErrorCreditCard()
    {
        global $ERROR_GMO;
        $errCode = Globals::get('errcode');
        $data = '';

        if (array_key_exists($errCode, $ERROR_GMO)) {
            $data = $ERROR_GMO[$errCode];
        }else{
            $data = "支払い処理中にエラーが発生しました";
        }
        jsonEncode($data);
    }

    static function checkOrder(){
	    global $sql;

	    $order = Globals::get('order_id');
	    $table = "faq";
	    $where = $sql->setWhere($table, null, "order_id","=",$order);
        $where = $sql->setWhere($table, $where, "group_id","=",Globals::get('group_id'));
        if($id = Globals::get('id')) {
            $where = $sql->setWhere($table, $where, "id", "!=", $id);
        }
        $result = $sql->getRow($table,$where);
	    if($result > 0){
            jsonEncode("注文番号は既に存在します");
        }
        jsonEncode('');
    }

    static function addCouponPoint()
    {
        global $sql;
        global $NOT_SEND_MAIL;
        $data = Globals::get();
        $list = array();
        if (!empty($data["list"])) {
            $list = explode("/", $data["list"]);
        } elseif (!empty($data["list_all"])) {
            $list = explode("/", $data["list_all"]);
        }
        $pay_table = 'pay';
        $expiry = mktime(0, 0, 0, $data["expire_M"], $data["expire_D"], $data["expire_Y"]);

        $pay = array();
        foreach ($list as $key => $val) {
            $user = $sql->selectRecord('user', $val);
            $pay_id    = SystemUtil::getUniqId($pay_table, false, true);
            $pay['name']      = $user['name'];
            $pay['user'] = $val;
            $pay['pay_num']   = $data['coupon_label'];
            $pay['policy_check']   = 3;
            $pay['pay_state']   = 1;
            $pay['delivery_state']   = 1;
            $pay['pending']   = 2;
            $pay['pay_total']   = -1;

            $pay_rec = $sql->setData($pay_table, null, "id", $pay_id);

            foreach ($pay as $key_pay => $value) {
                $pay_rec = $sql->setData($pay_table, $pay_rec, $key_pay, $value);
            }

            $sql->addRecord($pay_table, $pay_rec);
            $point_id = createUpoint($val, $data['point'], $pay_id, UPOINT_STATE['available'], $expiry);
            $update_point_user = $sql->setData("user", null, "point", $data['point'] + $user['point']);
            $sql->updateRecord("user", $update_point_user, $val);
            if ($data['state'] == '1') {
                $point_donate = array(
                    'name' => $user['name'],
                    'point_donate' => $data['point'],
                    'point' => $data['point'] + $user['point'],
                    'day' => $data["expire_D"],
                    'month' => $data["expire_M"],
                );
                if (!in_array($user["mail"], $NOT_SEND_MAIL)) {
                    mail_templateFunc::sendMail("user", 'auto_mail_donate_points', $user["mail"], $point_donate);
                }
            }
            $coupon_log = $sql->setData("coupon_log", null, "user", $val);
            $coupon_log = $sql->setData("coupon_log", $coupon_log, "point_id", $point_id);
            $coupon_log = $sql->setData("coupon_log", $coupon_log, "coupon_label", $data['coupon_label']);
            $coupon_log = $sql->setData("coupon_log", $coupon_log, "point", $data['point']);
            $coupon_log = $sql->setData("coupon_log", $coupon_log, "regist_unix", time());
            $sql->addRecord("coupon_log", $coupon_log);
        }
        HttpUtil::location("/view.php?type=coupon_log");
    }

    static function getCodeTypeCoupon()
    {
        global $sql;
        $data = Globals::get();
        $result = [];
        if(!empty($data) && isset($data['type'])) {
            switch ($data['type']) {
                case 1:
                    $query = "SELECT `id`, `name` FROM master_item_web_categories";
                    $results = $sql->rawQuery($query);
                    while ($result_tmp = $sql->sql_fetch_assoc($results)) {
                        $result['category'][$result_tmp['id']] = $result_tmp['name'];
                    }
                    break;
                case 2:
                    $query = "SELECT `id`, `name` FROM master_item_type";
                    $results = $sql->rawQuery($query);
                    while ($result_tmp = $sql->sql_fetch_assoc($results)) {
                       $result['item'][$result_tmp['id']] = $result_tmp['name'];
                    }
                    break;
                case 3:
                    $queryC = "SELECT `id`, `name` FROM master_item_web_categories";
                    $queryI = "SELECT `id`, `name` FROM master_item_type";
                    $results['category'] = $sql->rawQuery($queryC);
                    while ($result_tmpC = $sql->sql_fetch_assoc($results['category'])) {
                        $result['category'][$result_tmpC['id']] = $result_tmpC['name'];
                    }
                    $results['item'] = $sql->rawQuery($queryI);
                    while ($result_tmpI = $sql->sql_fetch_assoc($results['item'])) {
                        $result['item'][$result_tmpI['id']] = $result_tmpI['name'];
                    }
                    break;
                default:
                    break;
            }
        }
        jsonEncode($result);
    }

    static function addListWillMail()
    {
        global $sql;
        $table = 'willmail_target';
        $request = Globals::request();
        if(!isset($request['db_target_id']) || !isset($request['willmail_list_id']))
        {
            jsonEncode('Error!');
        }
        $query = Globals::getQueryWillmailTarget($request['willmail_list_id']);
        $data = $sql->rawQuery($query);
        $API = ApiConfig::API_WILLMAIL.sprintf('/customers/%s/%s/insert', ACCOUNT_KEY_WILLMAIL, $request['db_target_id']);
        foreach ($data as $key => $value) {
            $postdata = [
                'to' => $value['user_email'],
                'from' => '',
                'content' => '',
                'field_5' => $value['user_email'],
                'field_1' => $value['user_email']
            ];
            $postdata = json_encode($postdata);
            $ch = curl_init($API);
            $headers = array(
                'Content-Type:application/json',
                'Authorization: Basic '. base64_encode(ACCOUNT_KEY_WILLMAIL.":".API_KEY_WILLMAIL)
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
            curl_exec($ch);
            curl_close($ch);
        }
        jsonEncode('ファイルのアップロードに成功しました。');
    }

    static function editListWillMail()
    {
        global $sql;
        $table = 'willmail_list';
        $request = Globals::request();
        if(!isset($request['id']) || !isset($request['db_target_id']) || !isset($request['name'])) {
            jsonEncode(['err' => 'Error !']);
        }
        if(!empty($request)) {
            $update = $sql->setData($table, null, "name", $request['name']);
            $update = $sql->setData($table, $update, "db_target_id", $request['db_target_id']);
            $sql->updateRecord($table, $update, $request["id"]);
        }
        jsonEncode($request);
    }

    static function addItemWillmail()
    {
        global $sql;
        $table = 'willmail_target';
        $request = Globals::request();
        if(!isset($request['willmail_list_id']) || !isset($request['email'])) {
            jsonEncode(['err' => 'Error !']);
        }
        if(!empty($request)) {
            $update = $sql->setData($table, null, "willmail_list_id", $request['willmail_list_id']);
            $update = $sql->setData($table, $update, "email", $request['email']);
            $sql->addRecord($table, $update);
        }
        jsonEncode($request);
    }

    static function readFileCsvWillMail($import_file)
    {
        $data_CSV = [];
        if (is_file($import_file)) {
            $extension = pathinfo($import_file, PATHINFO_EXTENSION);
            if ($extension != "csv") {
                Globals::setSession("import_message", "CSVファイルをアップロードしてください。1");
                unlink($import_file);
                return ;
            }
        }
        if (($handle = fopen($import_file, "r")) !== false) {
            $count = 0;
            while (($data = fgetcsv_reg($handle, 8000, ",")) !== false) {
                $count++;
                mb_convert_variables(mb_internal_encoding(), "SJIS-win", $data);
                //１行目スキップ
                if ($count == 1) continue;
                $data_CSV[] = $data[0];
            }
        }
        return $data_CSV;
    }

    static function orderAgainformhis()
    {
        global $sql;
        if (!empty(Globals::get("payOldeId"))) {
            $query_get_item = "SELECT * FROM pay_item WHERE pay = '" . Globals::get("payOldeId") . "' ORDER BY pay_item_num ASC";
            $result = $sql->rawQuery($query_get_item);

            while ($orders_item = $sql->sql_fetch_assoc($result)) {
                Globals::setGet('id', $orders_item["item"]);
                Globals::setGet('quantity', $orders_item["item_row"]);
                Globals::setGet('color', $orders_item["item_type_sub"]);
                Globals::setGet('size', $orders_item["item_type_size"]);
                Globals::setPost('multiple', true);
                if($orders_item['product_type'] == RINGPASRAL){
                    self::appliPasralAgain();
                }else{
                    self::web2web();
                }
            }

            HttpUtil::location('page.php?p=cart');
        }
    }

    static function createPromotionCode()
    {
        global $sql;
        $data = Globals::get();
        $code = array();
        ini_set('memory_limit', '200m');

        /*create code promotion*/
        $row = 1;
        while($row <= $data['amount']) {
            $chars = 'ABCDEFGHIJKLMNOQPRSTUVWXYZ0123456789';
            $str = '';
            for ($i = 0; $i < 13; $i++) {
                $str .= $chars[rand(0, strlen($chars)-1)];
            }
            $code[] = $data['prefix'] . $str;
            $row++;
        }

        /*insert multiple record*/
        $query = " INSERT INTO promotion_code (`code`,`name`,`regist_unix`,`expire`,`discount`,`code_type`,`scope`,`discount_type`,`type`,`limit1`, `limit2`) VALUES";

        $regist_unix = time();
        $expire = mktime(0, 0, 0, $data["expire_M"], $data["expire_D"], $data["expire_Y"]);
        $data['limit1'] = (isset($data['type']) && ($data['type'] != 0)) ? $data['limit1'] : '';
        $data['limit2'] = (isset($data['type']) && ($data['type'] != 0)) ? $data['limit2'] : '';
        foreach ($code as $key => $value) {
            $query .= sprintf('("%s","%s",%s,%s,%s,%s,"%s","%s",%s,"%s","%s"),',$value,$data['name'],$regist_unix,$expire,$data['discount'],$data['code_type'], $data['scope'],$data['discount_type'], $data['type'], $data['limit1'], $data['limit2']);
        }
        $query = rtrim($query, ",");
        $sql->rawQuery($query);

        $filename = "クーポンコード".date("Y-m-d").".csv";
        $fields = array("コード","コード名","発行日","有効期限","割引金額","利用制限", "適用範囲","割引タイプ", "タイプ", "制限1", "制限2");

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        $path = fopen("php://output", 'w');
        fputcsv($path, $fields);

        foreach ($code as $key => $value) {
            $code_type = '一回のみ利用可能';
            if ($data['code_type'] != '0') {
                $code_type = '複数回利用可能';
            }
            $scope_str = '';
            if($data['scope'] == 'only') {
                $scope_str = '1点のみ';
            }
            if($data['scope'] == 'all') {
                $scope_str = '全点';
            }
            $contents = [$value, $data["name"], date("Y-m-d", $regist_unix), date("Y-m-d", $expire), $data["discount"], $code_type, $scope_str, $data["discount_type"], $data['type'], $data['limit1'], $data['limit2']];
            fputcsv($path, $contents);
        }
        exit;

    }

    static function updateOrder()
    {
        global $sql;
        global $gmo;
        $table_charge_log = 'charge_log';
        $url = sprintf('%s/regist.php?type=pay', ApiConfig::DOMAIN);

        if (!empty(Globals::get('order_id'))) {
            $order_id = Globals::get('order_id');
            $current_order_id = Globals::get('current_order_id');
        } else {
            $order_id = Globals::post('OrderID');
            $current_order_id = '';
        }

        $is_fail = true;
        $PAY_RAKUTEN = getPayInfo($order_id);
        $data        = $PAY_RAKUTEN;

        if (Globals::post('Status') == 'REGISTER') {
            $order_data = [];
            $is_fail = false;

            if (!empty(Globals::get('order_id'))) {
                $order_data = [
                    'order_id' => $order_id,
                    'current_order_id' => $current_order_id,
                    'AccessID' => $PAY_RAKUTEN['AccessID'],
                    'AccessPass' => $PAY_RAKUTEN['AccessPass'],
                    'AuAcceptCode' => Globals::post('AuAcceptCode'),
                ];
            }

            $data = gmoFunc::careerPayment($PAY_RAKUTEN, $PAY_RAKUTEN['cost'], $PAY_RAKUTEN['tax'], $order_data);

            if (!$data['error']) {
                $info_pay_rakuten = Globals::session('INFO_RAKUTEN');

                $rec_pay_session = $sql->setData('pay_session', null, "pay", $info_pay_rakuten['OrderID']);
                $rec_pay_session = $sql->setData('pay_session', $rec_pay_session, "session", serialize(Globals::session()));
                $sql->addRecord('pay_session', $rec_pay_session);

                HttpUtil::postLocation($data['StartURL'], ['Token' => $data['Token'], 'AccessID' => $data['AccessID']]);
            } else {
                // $error .= sprintf('<li>決済に失敗しました。再度お試しください。(%s)</li>\n', $data['error_code']);
                Globals::setPost("step", Globals::post("step") - 1);
            }
        } elseif (Globals::post('Status') == 'AUTH') {
            $charge_log = $sql->keySelectRecord($table_charge_log, 'pay_id', $order_id);

            if (gmoFunc::updateCareerPayment($charge_log['id'], 1, true)) {
                $is_fail = false;
                $data['c'] = md5($gmo[2] . $order_id . $gmo[3]);
                $url       = sprintf('%s&back=true', $url);
            }
        }

        if ($is_fail) {
            $error_info = Globals::get('ErrInfo');
            $error_code = Globals::get('ErrCode');

            $rec_temp = $sql->keySelectRecord($table_charge_log, 'pay_id', $order_id);

            $update = $sql->setData($table_charge_log, null, "state", 3);
            $update = $sql->setData($table_charge_log, $update, "error_code", $error_code);
            $update = $sql->setData($table_charge_log, $update, "error_info", $error_info);
            $update = $sql->setData($table_charge_log, $update, "regist_unix", time());
            $sql->updateRecord($table_charge_log, $update, $rec_temp["id"]);

            changePayPay($order_id, 2);
        }

        HttpUtil::postLocation($url, $data);
    }

    static function addPromotionCode()
    {
        $code = Globals::get('code');
        $discount_promotion_code = 0;

        jsonEncode(update_promotion_code($code, $discount_promotion_code));
    }

    static function caculateTotalPriceNotItemBlank()
    {
        $total = getCartPrice();
        $cart = Globals::session("CART_ITEM");
        foreach ($cart as $key => $value) {
            if(self::checkItemBlank($value['item_id'])) {
                $total -= self::getPriceItemCart($value);
            }
        }
        return $total;
    }

    static function checkItemBlank($item)
    {
        $isBlank = false;
        if(isset($item['product_type']) && $item['product_type'] === 'bl') {
            $isBlank = true;
        }
        return $isBlank;
    }

    static function caculatePromotionLimit($rec, $no_expired = false)
    {
        global $sql;
        $table = 'item_web_categories';
        $discount = 0;
        $cart = Globals::session("CART_ITEM");
        $arr_item_allow = [];
        if(!empty($rec['limit1'])) {
            $rec['limit1'] = substr($rec['limit1'], 0, -1);
            $arr_limit = explode('/', $rec['limit1']);
            $where = $sql->setWhere($table, null, 'category','IN', $arr_limit);
            if(!empty($arr_limit)) {
                $query = $sql->getSelectResult($table,$where, null);
                while ($obj_item = $sql->sql_fetch_assoc($query)) {
                    $arr_item_allow[] = $obj_item['item_type'];
                }
            }
        }
        if(!empty($rec['limit2'])) {
            $rec['limit2'] = substr($rec['limit2'], 0, -1);
            $arr_limit = explode('/', $rec['limit2']);
            $arr_item_allow = array_merge($arr_item_allow, $arr_limit);
        }
        if(empty($arr_item_allow) && ($rec['type'] != 0)) {
            return $discount;
        }
        $check_used = ($rec['code_type'] == 0  && $rec['state'] != 0) ? true : false;
        if(isset($rec['expire']) && !empty($rec['expire']) && (time() <= $rec['expire']) && !$check_used || $no_expired) {
            foreach ($cart as $key => $value) {
                $total_item_price = self::getPriceItemCart($value);
                $check = ($rec['type'] == 0) ? true : in_array($value['item_type'], $arr_item_allow);
                if($check && !self::checkItemBlank($value)) {
                    $quantity = 0;

                    if($rec['scope'] == 'only') {
                        $quantity = 1;
                    } else {
                        foreach ($value['item_type_size_detail'] as $size) {
                            $quantity += $size['total'];
                        }
                    }

                    if ($rec['discount_type'] == 'value') {
                        $discount_rank = Extension::discountPrice([2 => 'discount_rank']);
                        $discount += min($rec['discount'] * $quantity, ($total_item_price - $discount_rank));
                    } else {
                        $discount += floor( $total_item_price * $rec['discount'] * $quantity);
                    }

                    if($rec['scope'] == 'only') {
                        break;
                    }
                }
            }
        }
        return $discount;
    }

    static function getPriceItemCart($item_value)
    {
        $item_total = 0;
        $isPrintByLayersActivated = isset($item_value["print_by_layers_activated"]) ? $item_value["print_by_layers_activated"] : '0';
        if (!$isPrintByLayersActivated) {
            $item_price = $item_value["cart_price"];
            $item_calc = $item_price * $item_value["cart_row"];
            if(isset($item_value["design_from"])) {
                if ($item_value["design_from"] == 'niko2') {
                    $item_calc = $item_calc * 0.8;
                }
            }
            $item_total += $item_calc;
        } else {
            $printByLayersPlatePrice = isset($item_value["print_by_layers_plate_price"]) ? $item_value["print_by_layers_plate_price"] : 0;
            $printByLayersItemPrice = isset($item_value["print_by_layers_item_price"]) ? $item_value["print_by_layers_item_price"] : 0;
            $printByLayersFrontPrice = isset($item_value["print_by_layers_front_price"]) ? $item_value["print_by_layers_front_price"] : 0;
            $printByLayersBackPrice = isset($item_value["print_by_layers_back_price"]) ? $item_value["print_by_layers_back_price"] : 0;
            $printByLayersLeftPrice = isset($item_value["print_by_layers_left_price"]) ? $item_value["print_by_layers_left_price"] : 0;
            $printByLayersRightPrice = isset($item_value["print_by_layers_right_price"]) ? $item_value["print_by_layers_right_price"] : 0;
            $printByLayersTotalPrice = $printByLayersPlatePrice + (($printByLayersItemPrice + $printByLayersFrontPrice +
                        $printByLayersBackPrice + $printByLayersLeftPrice + $printByLayersRightPrice) * $item_value["cart_row"]);
            if(isset($item_value["design_from"])) {
                if ($item_value["design_from"] == 'niko2') {
                    $printByLayersTotalPrice = $printByLayersTotalPrice * 0.8;
                }
            }
            $item_total += $printByLayersTotalPrice;
        }
        if($item_value["item_type"]=='IT303' || $item_value["item_type"]=='IT304'
            || $item_value["item_type"]=='IT305'  || $item_value["item_type"]=='IT306'
            || $item_value["item_type"]=='IT307'  || $item_value["item_type"]=='IT309'){
            $side_chinchi_fee = isset($item_value["side_chinchi"]['fee']) ? $item_value["side_chinchi"]['fee'] : 0;
            $upper_tip_fee = isset($item_value["upper_tip"]['fee']) ? $item_value["upper_tip"]['fee'] : 0;
            $chichi_color_fee = isset($item_value["chichi_color"]['fee']) ? $item_value["chichi_color"]['fee'] : 0;
            $deformation_cut_fee = isset($item_value["deformation_cut"]['fee']) ? $item_value["deformation_cut"]['fee'] : 0;
            $discount_niko2 = 1;
            if(isset($item_value["design_from"])) {
                if ($item_value["design_from"] == 'niko2') {
                    $discount_niko2 = 0.8;
                }
            }
            $item_total = $item_total + (($side_chinchi_fee + $upper_tip_fee + $chichi_color_fee + $deformation_cut_fee) * $item_value["cart_row"] *$discount_niko2);
        }
        return $item_total;
    }

    static function removePromotionCode()
    {
        $data = array();

        Globals::setSession("discount_promotion_code", 0);
        Globals::setSession("promotion_code_id", '');

        $data['total']= getUpPoint();
        $data['point']= Extension::discountPrice([2 => 'point']);
        $data['msg'] = '';

        jsonEncode($data);
    }

    static function getTokenCode()
    {
        $tc = SystemUtil::setTokenCode(Globals::get("method") ,Globals::get("type"));
        jsonEncode($tc);
    }

    static function receiveGmoNotifications()
    {
        global $sql;

        $table_charge_log = 'charge_log';
        $data_return['state'] = 0;
        $pay_type = Globals::get("paytype");
        $order = Globals::get("order");
        $status = Globals::get("status");
        $error_info = Globals::get("error_info");
        $error_code = Globals::get("error_code");

        if ($pay_type == 18) {
            $rec_temp = $sql->keySelectRecord($table_charge_log, "pay_id", $order);

            switch ($status) {
                case 'PAYFAIL':
                    /*update state charge_log*/
                    $update = $sql->setData($table_charge_log, null, "state", 3);
                    $update = $sql->setData($table_charge_log, $update, "error_code", $error_code);
                    $update = $sql->setData($table_charge_log, $update, "error_info", $error_info);
                    $update = $sql->setData($table_charge_log, $update, "regist_unix", time());
                    $sql->updateRecord($table_charge_log, $update, $rec_temp["id"]);

                    /*update state pay*/
                    if (!changePayPay($order, 2, true, 'PAYFAIL')) {
                        $data_return['state'] = 1;
                    }
                    break;
                case 'SALES':

                    /*update state pay*/
                    if (!changePayPay($order, 1, true)) {
                        $data_return['state'] = 1;
                    };
                    break;
                case 'CANCEL':

                    /*update state pay*/
                    if (!changePayPay($order, 2, true)) {
                        $data_return['state'] = 1;
                    };

                    break;
                case 'CANCELFAIL':
                    /*update state charge_log*/
                    $update = $sql->setData($table_charge_log, null, "state", 6);
                    $update = $sql->setData($table_charge_log, $update, "error_code", $error_code);
                    $update = $sql->setData($table_charge_log, $update, "error_info", $error_info);
                    $update = $sql->setData($table_charge_log, $update, "regist_unix", time());
                    $sql->updateRecord($table_charge_log, $update, $rec_temp["id"]);

                    break;
                default:
                    break;
            }

        }

        echo json_encode($data_return); exit;
    }

    static function upload_img_post()
    {
        $file_url = SystemUtil::uploadImage($_FILES["upload"]["name"], $_FILES["upload"]["tmp_name"], $_FILES["upload"]['type'], $message);
        @header('Content-type: text/html; charset=utf-8');
        $CKEditorFuncNum = Globals::get('CKEditorFuncNum');
        echo "<script>window.parent.CKEDITOR.tools.callFunction($CKEditorFuncNum, '$file_url')</script>";
        exit;
    }

    static function changeItemSameDay()
    {
        global $sql;
        $id = Globals::get('id');
        $flag_same_day = Globals::get('flag_same_day');
        $table = "master_item_type";
        $data = [];

        $update = $sql->setData($table, null, "flag_same_day", $flag_same_day);
        if (!$rec = $sql->updateRecord($table, $update, $id)) {
            $data['msg'] = '予期せぬエラーが発生しました。';
        } else {
            $data['msg'] = '変更に成功しました。';
        };

        echo json_encode($data);
        exit;
    }

    static function sendDate()
    {
        $data = [];
        if(isset($_GET['day'])) {
            $date = $_GET['day'];
            if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$date)) {
                $dateValidate = true;
            } else {
                $dateValidate = false;
            }
            //validate method $_GET input is date
            if ($dateValidate) {
                http_response_code (200);

                if($date == date("Y-m-d")) {
                    $send_date = date("Y-m-d H:i");
                } else {
                    $send_date = date("Y-m-d 08:00", strtotime($date));
                }

                $data['date_delivery'] = calc_senddate($send_date, false, false,true, true)[1];
                $data['date_normal'] = calc_senddate($send_date, false, false,false, true)[1];
            } else {
                http_response_code (400);
            }
        } else {
            http_response_code (400);
        }

        echo json_encode($data);
    }

    /*create checkout session amazon pay v2*/
    static function createCheckoutSessionAmazon()
    {
        global $amazonpay_config;
        global $STORE_ID_AMAZON;

        Globals::setSession('idempotency_key',"");
        $checkout_review_return_url = 'https://up-t.jp/regist.php?type=pay';
        $idempotency_key = uniqid();

        Globals::setSession('idempotency_key', $idempotency_key);

        $webCheckoutDetail = array('checkoutReviewReturnUrl' => $checkout_review_return_url);

        $headers = array('x-amz-pay-Idempotency-Key' => $idempotency_key);

        $payload = array_merge(
            array('webCheckoutDetail' => $webCheckoutDetail),
            array('storeId' => $STORE_ID_AMAZON)
        );

        try {
            $client = new AmazonPayV2\Client($amazonpay_config);
            $result = $client->createCheckoutSession($payload, $headers);
            $response = json_decode($result['response']);
            $checkout_session_id = $response->checkoutSessionId;
            $return_string = '{"amazonCheckoutSessionId":"' . $checkout_session_id . '"}';
            echo($return_string);

        } catch (\Exception $e) {
            // handle the exception
            echo $e . "\n";
        }
    }

    /*update checkout session amazon pay v2*/
    static function updateCheckoutSessionAmazon()
    {
        global $amazonpay_config;

        $amount = Globals::session('cost_amazon');
        $pay_num = getPayNum();
        Globals::setSession('PAY_NUM', $pay_num);
        $checkout_session_id = Globals::session('amazonCheckoutSessionId');
        $payload = array(
            'webCheckoutDetail' => array(
                'checkoutResultReturnUrl' => 'https://up-t.jp/regist.php?type=pay&back=true'
            ),
            'paymentDetail' => array(
                'paymentIntent' => 'Authorize',
                'canHandlePendingAuthorization' => false,
                'chargeAmount' => array(
                    'amount' => $amount,
                    'currencyCode' => "JPY"
                ),
            ),
            'merchantMetadata' => array(
                'merchantReferenceId' => $pay_num,
                'merchantStoreName' => 'UP-T',
                'noteToBuyer' => 'Thank you for your order!'
            )
        );

        try {
            $client = new AmazonPayV2\Client($amazonpay_config);
            $result = $client->updateCheckoutSession($checkout_session_id, $payload);
            $url = json_decode($result['response'])->webCheckoutDetail->amazonPayRedirectUrl;

            HttpUtil::location($url);

        } catch (\Exception $e) {
            echo $e . "\n";
        }

        exit();
    }

    static function saveDataFromLocalStorage()
    {

        $data = json_decode(Globals::get("data"));
        $data_session = array();
        $data_return['state'] = 0;

        foreach ($data as $key => $value) {
            $data_session[$value->name] = $value->value;
        }
        $data = array_merge(Globals::session('AMAZON_PAY'), $data_session);
        Globals::setSession('AMAZON_PAY', $data);

        jsonEncode($data_return);
    }

    static function getSendDate($return = false, $is_new = false) {
        $send_date = getCachedContent('content', 'send_date', $is_new);

        if (!empty($send_date)) {
            if ($return) {
                return $send_date;
            }

            print $send_date;
            exit;
        }

        list($today, $recvdate) = calc_senddate(date("Y-m-d H:i"));
        list($today, $recvdate_fast) = calc_senddate(date("Y-m-d H:i"),false, false,true, true);
        list($today, $recvdate_6) = calc_senddate(date("Y-m-d H:i"),false, false,false, false,null,6);
        list($today, $recvdate_10) = calc_senddate(date("Y-m-d H:i"),false, false,false, false,null,10);
        list($today, $recvdate_30) = calc_senddate(date("Y-m-d H:i"),false, false,false, false,null,30);

        $data = [
            'order_date' => $today, //ORDER_DATE
            'receive_date' => $recvdate, //RECV_DATE
            'receive_date_6' => $recvdate_6,
            'receive_date_10' => $recvdate_10,
            'receive_date_30' => $recvdate_30,
            'receive_date_fast' => $recvdate_fast, //RECV_DATE_FAST
            'silk_receive_date' => calcSilkRecDate($recvdate), //SILK_RECV_DATE
            'receive_date_nobori' => calc_senddate(date("Y-m-d H:i"), false, true), //RECV_DATE_NOBORI
        ];

        if ($return) {
            return json_encode($data);
        }

        jsonEncode($data);
    }

    static function addItemWishList()
    {
        global $sql;

        $user = Globals::session('LOGIN_ID');

        // check exist wish List

        $id_wish_list = checkUserWishList($user);

        //add item to wishlist

        $rec = $sql->setData("item_wish_list",null,'wish_list',$id_wish_list);
        $rec = $sql->setData("item_wish_list",$rec,'item',Globals::get("item"));
        $rec = $sql->setData("item_wish_list",$rec,'regist_unix',time());

        $sql->addRecord("item_wish_list", $rec);

        HttpUtil::location('/search.php?type=item_wish_list');
    }

    static function removeItemWishList()
    {
        global $sql;

        $rec = findItemWishList(Globals::get("item"));

        //remove record
        $sql->deleteRecord("item_wish_list", $rec['id']);

        HttpUtil::location('/search.php?type=item_wish_list');

    }

    static function createWishListNewByUser()
    {
        global $sql;
        $data = array();
        if (empty(Globals::get('name'))) {
            $data['msg'] = 'ホイストリスト名を入力してください';
        }

        if (empty($data)) {
            $table = 'wish_list';
            $user = Globals::session('LOGIN_ID');

            $id = SystemUtil::getUniqId($table, false, true);
            $rec = $sql->setData($table,null,'user',$user);
            $rec = $sql->setData($table,$rec,'id',$id);
            $rec = $sql->setData($table,$rec,'name',Globals::get('name'));
            $rec = $sql->setData($table,$rec,'status',Globals::get('status'));
            $rec = $sql->setData($table,$rec,'regist_unix',time());

            $sql->addRecord($table, $rec);
            $data['msg'] = 'ホイストリストが正常に作成されました';
        }

        jsonEncode($data);

    }

    static function findWishList()
    {
        global $sql;

        $data = [
            'id' => '',
            'name' => '',
            'status' => '',
            'url' => ''
        ];

        $wish_list = $sql->selectRecord('wish_list', Globals::get("id"));
        if (!empty($wish_list)) {
            $data = [
                'id' => $wish_list['id'],
                'name' => $wish_list['name'],
                'status' => $wish_list['status'],
                'url' => sprintf('%s/search.php?type=wish_list&session=%s',ApiConfig::DOMAIN,$wish_list['id'])
            ];
        }

        jsonEncode($data);
    }

    static function changeItemWishList()
    {
        global $sql;

        $table = 'item_wish_list';
        $data = array();

        $update = $sql->setData($table,null,'wish_list',Globals::get('wish_list'));
        $where = $sql->setWhere($table, null, "item", "=", Globals::get('item'));
        $where = $sql->setWhere($table, $where, "wish_list", "IN", sprintf("(SELECT id FROM wish_list WHERE wish_list.`user` = '%s')",Globals::session('LOGIN_ID')));

        $sql->updateRecordWhere($table, $update, $where);
        $data['msg'] = 'ホイストリストのアイテムの更新に成功しました';
        jsonEncode($data);
    }

    static function changeMultiItemRakuten()
    {
        global $sql;

        $data = array();
        $table = Globals::get('type');
        $ids = str_replace('/', '","', Globals::get('list'));
        $state = Globals::get('state');

        $where = $sql->setWhere($table, null, 'id', 'IN', '"'.$ids.'"');
        $update = $sql->setData($table, null, 'rakuten', $state);

        $sql->updateRecordWhere($table, $update, $where);

        $data['msg'] = 'Operation performed successfully!';
        jsonEncode($data);

    }

    static function getProductDefault()
    {
        global $cc;
        global $sql;
        $tmp = '';
        $item_table = 'master_item_type';
        $item_sub_table = 'master_item_type_sub';
        $items = array();

        $template = SystemUtil::getPartsTemplate("price_report", 'list');

        $rec = $sql->selectRecord($item_table, Globals::get('item'));

        $sub_where = $sql->setWhere($item_sub_table, null, "item_type", "=", Globals::get('item'));
        $sub_where = $sql->setWhere($item_sub_table, $sub_where, "is_main", "=", 1);
        $sub_where = $sql->setWhere($item_sub_table, $sub_where, "state", "=", 1);

        $rec_sub = $sql->sql_fetch_assoc($sql->getSelectResult($item_sub_table, $sub_where));

        if (!empty(Globals::session('ITEM_PRICE')) && array_key_exists($rec['id'], Globals::session('ITEM_PRICE'))) {

            $data_session = Globals::session('ITEM_PRICE');
            $data_session[$rec['id']]['total'] += 1;
            Globals::setSession('ITEM_PRICE', $data_session);

            $price_report = calculatePriceProductReport($data_session[$rec['id']]['price'],  1, true);

            $price_report['template'] = 'exist';
            $price_report['item'] = $rec['id'];
            $price_report['item_total'] = $data_session[$rec['id']]['total'];

        } else {

            $rec['item_sub'] = $rec_sub['id'];
            $rec['preview_url'] = $rec_sub['thumbnail_url'];
            $rec['total'] = 1;

            $items[$rec['id']]['item'] = $rec['id'];
            $items[$rec['id']]['name'] = $rec['name'];
            $items[$rec['id']]['preview_url'] = $rec['preview_url'];
            $items[$rec['id']]['total'] = $rec['total'];
            $items[$rec['id']]['price'] = $rec['item_price'];
            $items[$rec['id']]['sale_price'] = $rec['tool_price'] * $rec['sale_price'];
            $items[$rec['id']]['tool_price'] = $rec['tool_price'];
            $items[$rec['id']]['item_sub'] = $rec['item_sub'];

            if (!empty(Globals::session('ITEM_PRICE'))) {

                $data_session = Globals::session('ITEM_PRICE');
                $data_session[$rec['id']] = $items[$rec['id']];
                Globals::setSession('ITEM_PRICE', $data_session);
            } else {

                Globals::setSession('ITEM_PRICE', $items);
            }

            $price_report = calculatePriceProductReport($rec['item_price'], $rec['total'], true);

            $tmp .= $cc->run($template, $rec);
            $price_report['template'] = $tmp;
        }
        $tools = array_keys(Globals::session('ITEM_PRICE'));
        $price_report['url_tool'] = Extension::getDrawToolLinkString(null, null, null, $tools[0]);

        $price_report = array_merge($price_report, calculateTotalPriceIgnoreDiscount());
        jsonEncode($price_report);

    }

    static function changeColorProduct()
    {
        $old_sides_price = '';
        $new_sides_price = '';
        $item_type = '';
        $item_sub = '';
        $thumbnail_url = '';

        $result = getPreviewSide(Globals::get('item_sub'));

        if (!empty(Globals::session('ITEM_PRICE'))) {

            $session = Globals::session('ITEM_PRICE');

            foreach ($result as $key => $value) {

                if (isset($session[$value['item_type']]['side'][$key]) && $session[$value['item_type']]['side'][$key] != '') {
                    $old_sides_price += $session[$value['item_type']]['side'][$key];
                    $new_sides_price += $value['printPrice'];
                    $session[$value['item_type']]['side'][$key] = $value['printPrice'];
                }

                $item_type = $value['item_type'];
                $item_sub = $value['item_sub'];
                $thumbnail_url = $value['thumbnail_url'];
            }

            $session[$item_type]['item_sub'] = $item_sub;
            $session[$item_type]['preview_url'] = $thumbnail_url;

            if (!empty(Globals::session('PRICE_REPORT'))) {

                $price_report = Globals::session('PRICE_REPORT');
                $update_price = $price_report['price_total'] + (($new_sides_price + $session[$item_type]['price']) - ($old_sides_price + $session[$item_type]['price'])) * $session[$item_type]['total'];
                $update_price_report = discountByTotal($update_price, $price_report['total']);
                Globals::setSession('PRICE_REPORT', $update_price_report);
                $update_price_report = array_merge($update_price_report, calculateTotalPriceIgnoreDiscount());
                $result['price_report'] = $update_price_report;
            }
            Globals::setSession('ITEM_PRICE', $session);
        }

        return print_r(json_encode($result));
    }

    static function removeProduct()
    {
        $item = Globals::get('item');
        $session = Globals::session('ITEM_PRICE');
        $price_report = Globals::session('PRICE_REPORT');

        if (!empty($session[$item]['side'])) {
            $price_sides = 0;
            foreach ($session[$item]['side'] as $key => $value) {
                $price_sides += $value;
            }

            $update_price = $price_report['price_total'] - (($price_sides + $session[$item]['price']) * $session[$item]['total']);
            $update_price_report = discountByTotal($update_price, $price_report['total'] - $session[$item]['total']);
            Globals::setSession('PRICE_REPORT', $update_price_report);
            $price_report = $update_price_report;
        } else {

            $price_report = calculatePriceProductReport($session[$item]['price'], $session[$item]['total'], false);
        }

        unset($session[$item]);

        Globals::setSession('ITEM_PRICE', $session);

        if (empty($session)) {
            $price_report['url_tool'] = DrawToolConfig::HOST;
            $price_report['noti'] = '<p class="pr_noti">アイテムが選択されていません。</p>';
        } else {
            $tools = array_keys($session);
            $price_report['url_tool'] = Extension::getDrawToolLinkString(null, null, null, $tools[0]);
        }

        $price_report['msg'] = 'OK';
        $price_report = array_merge($price_report, calculateTotalPriceIgnoreDiscount());
        jsonEncode($price_report);
    }

    static function pickSideProduct()
    {
        $item = Globals::get('item');
        $side = Globals::get('side');
        $price = Globals::get('price');
        $checked = Globals::get('checked');

        $session = Globals::session('ITEM_PRICE');
        $price_report = Globals::session('PRICE_REPORT');

        if ($checked == 'true') {

            $session[$item]['side'][$side] = $price;
            $update_price = $price_report['price_total'] + ($price * $session[$item]['total']);
            $update_price_report = discountByTotal($update_price,$price_report['total']);
        } else {

            unset($session[$item]['side'][$side]);
            $update_price = $price_report['price_total'] - ($price * $session[$item]['total']);
            $update_price_report = discountByTotal($update_price,$price_report['total']);
        }
        Globals::setSession('ITEM_PRICE', $session);
        Globals::setSession('PRICE_REPORT', $update_price_report);
        $update_price_report = array_merge($update_price_report, calculateTotalPriceIgnoreDiscount());
        jsonEncode($update_price_report);
    }

    static function changeTotalProduct()
    {
        $data = array();

        $item = Globals::get('item');

        $new_total = Globals::get('total');

        if($new_total == '' || $item == ''){
            $data['msg'] = 'fail';
            jsonEncode($data);
        }

        $session = Globals::session('ITEM_PRICE');

        $total = $new_total - $session[$item]['total'];

        if(!empty($session[$item]['side'])){
            $price_sides = 0;
            foreach ($session[$item]['side'] as $key => $value){
                $price_sides += $value;
            }
            $prices = $session[$item]['price'] + $price_sides;
        } else {
            $prices = $session[$item]['price'];
        }

        $price_report = calculatePriceProductReport($prices,  $total, true);

        $session[$item]['total'] = $new_total;

        Globals::setSession('ITEM_PRICE',$session);

        $price_report['msg'] = 'OK';
        $price_report = array_merge($price_report, calculateTotalPriceIgnoreDiscount());
        jsonEncode($price_report);
    }

    static function searchProductsReport()
    {
        global $sql;
        global $cc;

        $data = array();
        $table = 'master_item_type';
        $search = strtoupper(trim(Globals::get('text')));

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $clume = $sql->setClume($table, $clume, 'maker');
        $clume = $sql->setClume($table, $clume, 'item_code_nominal');
        $clume = $sql->setClume($table, $clume, 'color_total');
        $clume = $sql->setClume($table, $clume, 'size');
        $clume = $sql->setClume($table, $clume, 'material');
        $clume = $sql->setClume($table, $clume, 'tool_price');
        $clume = $sql->setClume($table, $clume, 'sale_price');
        $clume = $sql->setClume($table, $clume, 'preview_url');
        $clume = $sql->setClume('master_item_type_sub', $clume, 'thumbnail_url');

        $inner_join = $sql->setInnerJoin('master_item_type_sub', 'master_item_type', 'id', 'master_item_type_sub', 'item_type');
        $where = $sql->setWhere($table, null, 'name', "LIKE", "%" . $search . "%", "OR","(");
        $where = $sql->setWhere($table, $where, 'name', "LIKE", "%" . mb_convert_kana($search, "ASKV") . "%", "OR");
        $where = $sql->setWhere($table, $where, 'item_code', "=", $search, "OR");
        $where = $sql->setWhere($table, $where, 'item_code_nominal', "LIKE", "%" . $search . "%", "OR",")");
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, 'category_id', 'IN', '(SELECT id FROM master_categories WHERE master_categories.is_deleted = 0)');
        $where = $sql->setWhere('master_item_type_sub', $where, 'state', '=', 1);
        $where = $sql->setWhere('master_item_type_sub', $where, 'is_main', '=', 1);
        $order = $sql->setOrder($table, null, "id", "ASC");
        $result = $sql->getSelectResult($table, $where, $order, null, $clume, null, $inner_join);

        $tmp = '';
        $template = SystemUtil::getPartsTemplate("price_report", 'search');
        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (empty($rec['thumbnail_url'])) {
                $rec['thumbnail_url'] = $rec['preview_url'];
            }
            $tmp .= $cc->run($template, $rec);
        }
        $data['template'] = $tmp;

        jsonEncode($data);

    }

    static function findItemWebCategory()
    {
        global $sql;
        $data = array();
        $tmp_sub = '';
        $tmp_item = '';
        $id = Globals::get('id');
        $type = Globals::get('type');

        if ($type == 'category') {
            $where_sub = $sql->setWhere('master_item_web_sub_categories', null, 'parent', '=', $id);
            $where_sub = $sql->setWhere('master_item_web_sub_categories', $where_sub, 'state', '=', 0);

            $result_sub = $sql->getSelectResult('master_item_web_sub_categories', $where_sub);
            $tmp_sub .= '<option value="" selected>選択してください</option>';
            while ($rec = $sql->sql_fetch_assoc($result_sub)) {
                $tmp_sub .= sprintf('<option value="%s">%s</option>', $rec['id'], $rec['name']);
            }
            $data['sub_category'] = $tmp_sub;
        }

        $clume = $sql->setClume('master_item_type', null, 'name', null, 'item_name');
        $clume = $sql->setClume('master_item_type', $clume, 'id', null, 'item');
        $inner_join = $sql->setInnerJoin('master_item_type', 'item_web_categories', 'item_type', 'master_item_type', 'id');
        $where = $sql->setWhere('item_web_categories', null, $type, '=', $id);
        $where = $sql->setWhere('master_item_type', $where, 'state', '=', 1);

        $result = $sql->getSelectResult('item_web_categories', $where, null, null, $clume, null, $inner_join);

        $tmp_item .= '<option value="" selected>選択してください</option>';

        while ($rec = $sql->sql_fetch_assoc($result)) {

            $tmp_item .= sprintf('<option value="%s">%s</option>', $rec['item'], $rec['item_name']);

        }

        $data['item'] = $tmp_item;
        jsonEncode($data);
    }

    static function goToShop()
    {
        // update user_id in user_sessions when user go to cart with exist account
        global $sql;
        $table = "users_sessions";
        $where = $sql->setWhere($table, null, "token", "=", Globals::session('DRAW_TOOL_SESSION'));
        if ($result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where))) {
            if (empty($result['user_id']) && !empty(Globals::session("LOGIN_ID"))) {
                $sql->updateRecord($table, array("user_id" => Globals::session("LOGIN_ID")), $result['id']);
            }
        }

        $params = sprintf('is_admin=true&session=%s', Globals::session('DRAW_TOOL_SESSION'));

        if (!empty(Globals::get('url'))) {
            $params .= sprintf('&url=%s', urlencode(Globals::get('url')));
        }

        HttpUtil::location(sprintf('%s?%s', Globals::get('shop'), $params));
    }

    static function checkOrderIsBlankItemOnly() {
        ini_set('memory_limit', '128M');
        ini_set('max_execution_time', 0);
    	global $sql;
    	$data = array();
    	$ids = Globals::post('ids');
        $is_kanazawa = Globals::post('is_kanazawa');
        $table = "pay_item";
        $where = $sql->setWhere($table, null, 'pay', 'IN', $ids);
        $where = $sql->setWhere($table, $where, 'id', 'NOT IN', sprintf('SELECT id FROM pay_item WHERE product_type = "blank" AND pay IN ("%s")', implode('","',$ids)));
        $result = $sql->getSelectResult($table, $where);

        while ($pay_item = $sql->sql_fetch_assoc($result)) {
            $data[] = $pay_item['pay'];
        }

        if ((int)$is_kanazawa !== 1) {
            $is_kanazawa = 0;
        }

        if (!empty($data)) {
            $table = 'pay';
            $update  = $sql->setData($table, null, 'is_kanazawa', $is_kanazawa);
            $where = $sql->setWhere($table, null, 'id', 'IN', $data);
            $sql->updateRecordWhere($table, $update, $where);
        }

		jsonEncode($data);
	}

    static function updateSendMailStatus()
    {
        global $sql;

        $table = 'pay';
        $update = $sql->setData($table, null, "mail_status", (int)Globals::get("state"));
        $sql->updateRecord($table, $update, Globals::get("id"));

        jsonEncode(true);
    }

    static function approveReview()
    {
        $data["state"] = -1;

        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        if (!empty(Globals::get('id'))) {
            givePointReviews(Globals::get('id'), $state);
            $data["state"] = $state;

        } else {

            $order = '';
            if(!$list = Globals::get("list")) jsonEncode($data);

            $list = explode("/", $list);
            $count_list = count($list);

            for($i = 0; $i < $count_list; $i++)
            {
               $order = givePointReviews($list[$i], $state, $order);
            }
            $data["state"] = $state;
        }

        jsonEncode($data);
    }

    static function changeAdminstateItem()
    {
        $data = array();
        $data["state"] = -1;

        if(!$id = Globals::get("id")) jsonEncode($data);
        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        changeStateItemStore($id, $state);

        $data["state"] = $state + 0;
        jsonEncode($data);
    }

    static function getSendDateItemSameDay()
    {
        global $sql;
        $send_date = getCachedContent('content', 'send_date', false);

        if (!empty(Globals::session('CART_ITEM'))) {

            $item_same_day = same_day_master_item_type();
            $flag_same_day = true;
            $delivery_date = 0;
            foreach (Globals::session('CART_ITEM') as $value) {
                if (array_search($value['item_type'], $item_same_day) === false) {
                    $flag_same_day = false;
                }

                $item_type = $sql->selectRecord('master_item_type',$value['item_type']);
                if($item_type['delivery_date'] > $delivery_date) {
                    $delivery_date = $item_type['delivery_date'];
                }
            }
            if ($flag_same_day == true) {

                $date = new DateTime("now", new DateTimeZone('Asia/Tokyo'));
                $time_now = $date->format('H:i');
                $send_date = json_decode($send_date, true);

                if ($time_now < "09:00") {
                    $send_date['receive_date'] = $send_date['order_date'];
                } else {
                    $send_date['receive_date'] = $send_date['receive_date_fast'];
                }
                $send_date = json_encode($send_date);
            }

            if($delivery_date) {
                $send_date_check = obj2arr(json_decode($send_date));
                if($date_check = $send_date_check["receive_date_$delivery_date"]) {
                    $send_date_check['receive_date'] = $date_check;
                    $send_date = json_encode($send_date_check);
                }
            }
        }

        print $send_date;
        exit;
    }

    static function getMoreItem($return = false, $new = false, $path = self::ITEM_PATH)
    {
        global $cc, $design_template;
        $data = '';
        $page = Globals::get('page');

        if ($page == 'index_content') {
            $content =  $cc->run($design_template->getTemplate("index_content"));
            sp_replace_send_date($content);
            echo $content;
            exit;
        } else {
            $file = sprintf($path, $page);
        }

        if (!$new) {
            try {
                if (file_exists($file)) {
                    $data = file_get_contents($file);
                }
            } catch (Exception $exception) {
                // 0
            }
        }

        if (empty($data)) {
            getLiMasterItemWeb();
            $data = ccDraw::drawDisplayItemType([2 => 'sp', 3 => 'continue']);
        }

        if ($return) {
            return $data;
        }

        print $data;
        exit;
    }

    static function drawSelectSizeAndColorByItem()
    {
        global $sql;

        $data = array();
        $tables = array(0 => 'master_item_type_sub', 1 => 'master_item_type_size');
        $item = Globals::get('item');

        if (!empty($item)) {

            foreach ($tables as $key => $table) {

                $clume = $sql->setClume($table, null, 'item_code');
                $clume = $sql->setClume($table, $clume, 'name');

                $where = $sql->setWhere($table, null, 'item_type', '=', $item);
                $where = $sql->setWhere($table, $where, 'state', '=', 1);

                $result = $sql->getSelectResult($table, $where, null, null, $clume);

                $tmp = '';
                $tmp .= '<option value="">全て</option>';
                while ($rec = $sql->sql_fetch_assoc($result)) {
                    $tmp .= sprintf('<option value="%s">%s</option>', $rec['item_code'], $rec['name']);
                }
                $data[$table] = $tmp;
            }
        }

        jsonEncode($data);
    }

    static function drawSumStockItemBlank()
    {
        global $sql;
        $data = array();
        $itemId = Globals::get('item_type');
        $category = Globals::get('category');
        $sub_item = Globals::get('sub_item');
        $size_item = Globals::get('size_item');
        $sub_category = Globals::get('sub_category');
        $stock = Globals::get('stock');
        $free_word_search = Globals::get('free_word');

        $tmp = '<div class="purchase-table purchase-head">
                                <div class="col-stock stock-1">アイテム名</div>
                                <div class="col-stock stock-2">在庫数</div>
                                <div class="col-stock stock-3">サイズ・カラー別在庫</div>
                            </div>';

        $select = 'SELECT
                  master_item_type.`id`,
                  master_item_type.`name`,
                  item_web_categories.category,
                  item_web_categories.sub_category,
                  blank_item_stock.stock,
                SUM( blank_item_stock.stock ) AS sum
               FROM
                blank_item_stock';

        $where = '';

        if (!empty($itemId)) {

            $where .= sprintf(' WHERE master_item_type.`id` = \'%s\'', $itemId);

        } else {

            if (!empty($category)) {
                if (empty($where)) {

                    $where .= sprintf(' WHERE item_web_categories.`category` = \'%s\'', $category);
                } else {

                    $where .= sprintf(' AND item_web_categories.`category` = \'%s\'', $category);
                }
            }

            if (!empty($sub_category)) {
                if (empty($where)) {

                    $where .= sprintf(' WHERE item_web_categories.`sub_category` = \'%s\'', $sub_category);
                } else {

                    $where .= sprintf(' AND item_web_categories.`sub_category` = \'%s\'', $sub_category);
                }
            }
        }

        if (!empty($sub_item)) {
            if (empty($where)) {

                $where .= sprintf(' WHERE blank_item_stock.`item_sub_code` = \'%s\'', $sub_item);
            } else {

                $where .= sprintf(' AND blank_item_stock.`item_sub_code` = \'%s\'', $sub_item);
            }
        }

        if (!empty($size_item)) {
            if (empty($where)) {

                $where .= sprintf(' WHERE master_item_type_size.`name` = \'%s\'', $size_item);
            } else {

                $where .= sprintf(' AND master_item_type_size.`name` = \'%s\'', $size_item);
            }
        }
        if(!empty($free_word_search)){
            if(empty($where)){
                $where .= ' WHERE ( master_item_type.`name` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`maker` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`id` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`size` LIKE \'%'.$free_word_search.'%\')';
            }
            else{
                $where .=' AND ( master_item_type.`name` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`maker` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`id` LIKE \'%'.$free_word_search.'%\' OR master_item_type.`size` LIKE \'%'.$free_word_search.'%\')' ;
            }
        }

        $inner_join = ' INNER JOIN master_item_type ON master_item_type.item_code = blank_item_stock.item_code
                        INNER JOIN master_item_type_size ON master_item_type.id = master_item_type_size.item_type
	                   INNER JOIN item_web_categories ON item_web_categories.item_type = master_item_type.id';

        $group = ' GROUP BY master_item_type.`id`';

        $having = '';
        if (!empty($stock)) {

            $having .= sprintf(' HAVING SUM( blank_item_stock.stock ) >= %s', $stock);
        }

        $query = $select . $inner_join . $where . $group . $having;

        $result = $sql->rawQuery($query);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $stock = number_format($rec['sum']);
            $tmp .= sprintf('<div class="purchase-table purchase-body">
                                <div class="col-stock stock-1"><a href="%s">%s</a></div>
                                <div class="col-stock stock-2">%s</div>
                                <button class="col-stock stock-3 col-btn-stock" data-item="%s" data-color="%s" data-size="%s">一覧を開く</button>
                            </div>', ccDraw::itemCategories([2 => $rec['id']], $rec), $rec['name'], $stock, $rec['id'], $sub_item, $size_item);
        }
        $data['tmp'] = $tmp;

        jsonEncode($data);
    }

    static function drawPopupStockItemBlank()
    {
        $data = array();
        $itemId = Globals::get('item_type');
        $sub_item = Globals::get('color');
        $size_item = Globals::get('size');
        $tmp_table = '<table>';
        if (!empty($sub_item) && empty($size_item)) {

            $tmp_table .= ccDraw::drawListSizeAndSubBlankItem(array('color' => $sub_item, 2 => $itemId));
        } elseif (empty($sub_item) && !empty($size_item)) {

            $tmp_table .= ccDraw::drawListSizeAndSubBlankItem(array('size' => $size_item, 2 => $itemId));
        } else {

            $tmp_table .= ccDraw::drawListSizeAndSubBlankItem(array(2 => $itemId));
        }
        $tmp_table .= '</table>';
        $data['tmp_table'] = $tmp_table;

        jsonEncode($data);
    }

    static function changeThemeBuyState() {
        $id = Globals::get('id');
        $buy_state = Globals::get('buy_state');
        global $sql;
        $table = 'store_template';
        $update = $sql->setData($table,null,'buy_state',$buy_state);
        $sql->updateRecord($table,$update,$id);

        HttpUtil::location("/edit.php?type=store_template&id={$id}");
    }

    static function addImgPreviewTheme() {
        global $sql;
        $file = $_FILES['img-upload'];
        $theme_id = Globals::get('theme_id');
        $tmp = SystemUtil::doFileUpload($file, null, "./file/tmp/");

        $table = 'store_template_img';
        $rec = $sql->setData($table,null,'store_template_id',$theme_id);
        $rec = $sql->setData($table,$rec,'img_preview',$tmp);

        $sql->addRecord($table,$rec);

        $data['status'] = '200';

        jsonEncode($data);
    }

    static function removeImgPreviewTheme() {
        global $sql;
        $image_id = Globals::get('image_id');
        $table = 'store_template_img';
        $sql->deleteRecord($table, $image_id);
        $data['status'] = '200';

        jsonEncode($data);
    }

    static function applePayComm()
    {
        $validation_url = $_GET['u'];

        if( "https" == parse_url($validation_url, PHP_URL_SCHEME) && substr( parse_url($validation_url, PHP_URL_HOST), -10 )  == ".apple.com" ){

            require_once 'config/apple_pay/apple_pay_conf.php';

            // create a new cURL resource
            $ch = curl_init();

            $data = '{"merchantIdentifier":"'.PRODUCTION_MERCHANTIDENTIFIER.'", "domainName":"'.PRODUCTION_DOMAINNAME.'", "displayName":"'.PRODUCTION_DISPLAYNAME.'"}';

            curl_setopt($ch, CURLOPT_URL, $validation_url);
            curl_setopt($ch, CURLOPT_SSLCERT, PRODUCTION_CERTIFICATE_PATH);
            curl_setopt($ch, CURLOPT_SSLKEY, PRODUCTION_CERTIFICATE_KEY);
            curl_setopt($ch, CURLOPT_SSLKEYPASSWD, PRODUCTION_CERTIFICATE_KEY_PASS);
            //curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
            //curl_setopt($ch, CURLOPT_SSLVERSION, 'CURL_SSLVERSION_TLSv1_2');
            //curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'rsa_aes_128_gcm_sha_256,ecdhe_rsa_aes_128_gcm_sha_256');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

            if(curl_exec($ch) === false)
            {
                echo '{"curlError":"' . curl_error($ch) . '"}';
            }

            // close cURL resource, and free up system resources
            curl_close($ch);
        }
    }

    static function saveApplePayToken()
    {
        global $sql;

        $data = Globals::session('INFO_RAKUTEN');

        if (!empty($data) && !empty($data['pay_type']) && $data['pay_type'] == 'apple_pay') {
            jsonEncode(['status' => true]);
        }

        // merchant identifier from Apple Pay Merchant Account
        $appleId = PRODUCTION_MERCHANTIDENTIFIER;
        // how many seconds should the token be valid since the creation time.
        $expirationTime      = 315360000; // It should be changed in production to a reasonable value (a couple of minutes)
        $rootCertificatePath = 'config/apple_pay/AppleRootCA-G3.pem';
        // payment token data received from Apple Pay
        $paymentData = Globals::post('token')['paymentData'];
        $data        = ['post_data' => Globals::post()];
        $order       = self::_orderData(Globals::post('order'));

        $applePayDecodingServiceFactory = new ApplePayDecodingServiceFactory();
        $applePayDecodingService        = $applePayDecodingServiceFactory->make();
        $applePayValidator              = new ApplePayValidator();

        try {
            $applePayValidator->validatePaymentDataStructure($paymentData);
            $decodedToken = $applePayDecodingService->decode(PRIVATE_KEY, $appleId, $paymentData, $rootCertificatePath,
                $expirationTime);

            $result = gmoFunc::applePayment($order, $decodedToken->getTransactionAmount(), json_encode($paymentData));
            self::log_message(array_merge(['function' => 'saveApplePayToken'], $result));
            Globals::setSession('APPLE_RESULT', $result);

            if (!$result['error']) {
                jsonEncode(['status' => true]);
            }
        } catch (Exception $exception) {
            // nothing
        }

        jsonEncode(['status' => false]);
    }

    static function _orderData($data)
    {
        $order = [];

        foreach ($data as $input) {
            $order[$input['name']] = $input['value'];
        }

        return $order;
    }

    static function minify()
    {

        $data = [
            'sp' => [
                'nobody' => [
                    'css' => [
//                       'home' => [
//                           'common/smart/design/css/sp_home.css'
//                       ],
//                        'base'      => [
//                            'common/css/sp/cls.css',
//                            'common/smart/design/css/sp_style.css',
//                            'common/smart/design/css/new_style_renew_sp.css',
//                            'common/design/user/js/slick/slick.css',
//                            'common/css/swiper.min.css',
//                            'common/design/user/js/slick/slick-theme.css',
//                            'common/design/user/css/lightbox.css',
//                            'common/smart/design/css/style-top-page.css',
//                            'common/smart/design/css/common-top-page.css',
//                            'common/smart/design/css/style-index-add.css',
//                            'common/smart/design/css/item_detail.css',
//                            'common/smart/design/css/tabs.css',
//                            'common/design/user/css/sp_list_review.css'
//                        ],
//                        'cart'      => [
//                            'common/smart/design/css/sp_style.css',
//                            'common/smart/design/css/new_style_renew_sp.css',
//                            'common/smart/design/css/store-info-sp.css',
//                            'common/design/user/js/slick/slick.css',
//                            'common/css/swiper.min.css',
//                            'common/smart/design/css/style-top-page.css',
//                            'common/smart/design/css/common-top-page.css',
//                            'common/design/user/js/slick/slick-theme.css',
//                            'common/smart/design/css/tabs.css',
//                            'template/smart/html/page/nobody/cart/css/style-cart-sp.css',
//                            'common/design/user/css/lightbox.css',
//                            'common/design/user/css/sp_list_review.css'
//                        ],
//                        'design_nq' => [
//                            'common/smart/design/css/style.css',
//                            'common/css/libs/cssreset-min.css',
//                            'common/smart/design/css/new_style_renew_sp.css',
//                            'common/smart/design/css/style-top-page.css',
//                            'common/smart/design/css/pure-drawer.css',
//                            //'common/css/libs/font-awesome.min.css',
//                            'common/css/libs/pure-min.css',
//                            'common/smart/design/css/design_page.css',
//                            'common/smart/design/css/design_style2.css',
//                            //'common/css/swiper.min.css',
//                            //'common/design/user/css/lightbox.min8.css',
//                            //'common/smart/design/css/tabs.css',
//                        ],
//                        'design_qa' => [
//                            'common/design/user/css/style.css',
//                            'common/css/libs/cssreset-min.css',
//                            'common/css/libs/pure-min.css',
//                            'common/design/user/css/style_renew.css',
//                            //'common/design/user/js/slick/slick.css',
//                            //'common/css/libs/font-awesome.min.css',
//                            //'common/design/user/css/animate.css',
//                            'common/design/user/css/colorbox.css',
//                            //'common/design/user/css/tabs.css',
//                            //'common/design/user/css/flickslider.css',
//                            'common/design/user/css/designqa.css',
//                        ],
//                        'item' => [
//                            'common/design/user/js/slick/slick.css',
//                            'common/smart/design/css/item-detail.css',
//                        ],
//                        'detail_page' => [
//                            'common/smart/design/css/detail_page.css',
//                        ],
                    ],
                    'js'  => [
//                        'home' => [
//                            'common/js/sp/home.js'
//                        ],
//                        'base' => [
//                            'common/js/libs/jquery.matchHeight-min.js',
//                            'common/lib/underscore-min.js',
//                            'common/lib/jquery/jquery.tagcloud.js',
//                            'common/smart/design/js/smartphone.js',
//                            'common/smart/design/js/tabs.js',
//                            'common/smart/design/js/main.js',
//                            'common/design/user/js/search.base.js',
//                            'common/design/user/js/list_review.js',
//                            'common/js/smart_common.js',
//                            'common/js/sp_common.js',
//                            'common/design/user/js/accordion.js',
//                            'common/js/chat-button.js',
//                        ],
//                        'designenq' => [
//                            'common/lib/jquery/jquery-3.2.1.min.js',
//                            'common/lib/underscore-min.js',
//                            'common/js/smart_common.js',
//                            'common/smart/design/js/smartphone.js',
//                            'common/js/lazysizes.min.js',
//                            'common/js/sp/files/designenq-s-index.js',
//                        ],
//                        'designqa' => [
//                            'common/lib/jquery/jquery-3.2.1.min.js',
//                            'common/js/smart_common.js',
//                            'common/design/user/js/pagetop.js',
//                            'common/design/user/js/accordion.js',
//                            'common/lib/jquery/jquery.colorbox.js',
//                            'common/js/lazysizes.min.js',
//                        ],
//                        'item' => [
//                            'common/lib/underscore-min.js',
//                            'common/smart/design/js/smartphone.js',
//                            'common/js/item-detail.js',
//                            'common/design/user/js/lightbox-2.6.min.js',
//                        ],
//                        'detail_page' => [
//                            'common/js/sp/detail_page.js'
//                        ],
                    ],
                ],
                'user'   => [
                    'css' => [
//                        'base' => [
//                            'common/css/sp/cls.css',
//                            'common/smart/design/css/style.css',
//                            'common/smart/design/css/style_renew_sp.css',
//                            'common/smart/design/css/store-info-sp.css',
//                            'common/design/user/js/slick/slick.css',
//                            'common/css/swiper.min.css',
//                            'common/smart/design/css/style-top-page.css',
//                            'common/smart/design/css/common-top-page.css',
//                            'common/smart/design/css/style-index-add.css',
//                            'common/design/user/js/slick/slick-theme.css',
//                            'common/smart/design/css/item_detail.css',
//                            'common/smart/design/css/tabs.css',
//                            'common/design/user/css/lightbox.css',
//                            'common/design/user/css/sp_list_review.css'
//                        ],
//                        'cart' => [
//                            'common/smart/design/css/sp_style.css',
//                            'common/smart/design/css/new_style_renew_sp.css',
//                            'common/smart/design/css/store-info-sp.css',
//                            'common/design/user/js/slick/slick.css',
//                            'common/css/swiper.min.css',
//                            'common/smart/design/css/style-top-page.css',
//                            'common/smart/design/css/common-top-page.css',
//                            'common/design/user/js/slick/slick-theme.css',
//                            'common/smart/design/css/tabs.css',
//                            'template/smart/html/page/nobody/cart/css/style-cart-sp.css',
//                            'common/design/user/css/lightbox.css',
//                            'common/design/user/css/sp_list_review.css'
//                        ],
                    ],
                ],
            ],
            'pc' => [
                'nobody' => [
                    'css' => [
//                        'home' => [
//                            'common/css/pc/cls.css',
//                            'common/css/pc/files/template-pc-html-design-nobody-base-index.css',
//                            'common/design/user/css/item_detail.css',
//                            'common/design/user/css/list_review.css',
//                            'common/design/user/js/slick/slick-theme.css',
//                            'common/design/user/css/style-top-page.css',
//                            'common/design/user/css/style-index-add.css',
//                        ],
//                        'base' => [
//                            'common/css/pc/cls.css',
//                            'common/design/user/js/slick/slick.css',
//                            'common/design/user/css/style.css',
//                            'common/css/libs/cssreset-min.css',
//                            'common/design/user/css/style_renew.css',
//                            'common/css/swiper.min.css',
//                            'common/design/user/css/style-top-page.css',
//                            'common/design/user/css/style-common-new.css',
//                            'common/design/user/css/style-index-add.css',
//                        ],
//                        'cart' => [
//                            'common/design/user/css/style.css',
//                            'common/design/user/css/style_renew.css',
//                            'common/css/swiper.min.css',
//                            'common/design/user/css/style-top-page.css',
//                            'common/design/user/css/style-common-new.css',
//                        ],
//                        'design_nq' => [
//                            'common/design/user/css/style.css',
//                            'common/css/libs/cssreset-min.css',
//                            'common/css/libs/pure-min.css',
//                            'common/design/user/css/style_renew.css',
//                            'common/css/libs/font-awesome.min.css',
//                            'common/design/user/css/style-common-new.css',
//                            'common/design/user/css/style-top-page.css',
//                            'common/design/user/css/style-index-add.css',
//                            'common/design/user/css/design.css',
//                            'common/design/user/css/design_style2.css',
//                        ],
//                        'detail_page' => [
//                            'common/css/pc/detail_page.css'
//                        ],
                    ],
                    'js' => [
//                        'home' => [
//                            'common/lib/underscore-min.js',
//                            'common/design/user/js/slick/slick.min.js',
//                            'common/js/common.js',
//                            'common/design/user/js/main.js',
//                            'common/js/chat-button.js',
//                        ],
//                        'base' => [
//                            'common/lib/underscore-min.js',
//                            'common/design/user/js/slick/slick.min.js',
//                            'common/js/common.js',
//                            'common/design/user/js/main.js',
//                            'common/js/chat-button.js',
//                        ],
//                         'designenq' => [
//                            'common/lib/jquery/jquery-3.2.1.min.js',
//                            'common/lib/underscore-min.js',
//                            'common/js/common.js',
//                            'common/design/user/js/even.js',
//                            'common/js/pc/files/designenq-index.js',
//                        ],
//                        'detail_page' => [
//                            'common/js/pc/detail_page.js'
//                        ],
                    ]
                ],
            ],
        ];

        foreach ($data as $device_type => $user_types) {
            foreach ($user_types as $user_type => $file_types) {
                foreach ($file_types as $file_type => $screen_types) {
                    foreach ($screen_types as $screen_type => $paths) {
                        self::_minify($paths, $file_type, sprintf('common/%s/%s/%s_%s.min.%s', $file_type, $device_type, $user_type, $screen_type, $file_type));
                    }
                }
            }
        }

        self::_minifyFiles();

        echo 'Done';
    }

    private static function _minify($paths, $type, $minified_path)
    {
        if ($type == 'css') {
            $minifier = new Minify\CSS();
            $minifier->setImportExtensions([]);
            // $minifier->setMaxImportSize(1);
        } else {
            $minifier = new Minify\JS();
        }

        foreach ($paths as $path) {
            if (file_exists($path)) {
                $minifier->add($path);
            }
        }
        var_dump($minified_path);
        // save the minified file to disk
        $minifier->minify($minified_path);
    }

    static function addRakutenID()
    {
        global $sql;

        $design_id = Globals::get('design_id');
        $order_number = Globals::get('order_number');

        if(!empty($design_id) && !empty($order_number)){
            $table = "rakuten_design_id";

            $tmp_wheresearch = $sql->setWhere($table, null, "design_id", "=", $design_id);

            $result = $sql->getSelectResult($table, $tmp_wheresearch);


            if($result->num_rows >= 1){
                while($sub_result = $sql->sql_fetch_assoc($result)) {

                    $update = $sql->setData($table, null, "regist_unix", time());
                    $update = $sql->setData($table, $update, "order_number", $order_number);
                    $resultadd = $sql->updateRecord($table, $update, $sub_result['id']);
                }
            }
            else{
                $tmp_rec = $sql->setData($table, null, "design_id", $design_id);
                $tmp_rec = $sql->setData($table, $tmp_rec, "order_number", $order_number);
                $tmp_rec = $sql->setData($table, $tmp_rec, "regist_unix", time());
                if(Globals::session("LOGIN_TYPE")== 'user'){
                    $tmp_rec = $sql->setData($table, $tmp_rec, "user", Globals::session("LOGIN_ID"));
                }
                $resultadd = $sql->addRecord($table, $tmp_rec);
            }
        }
        if($resultadd == true){
            $GLOBALS['_SESSION']['CART_ITEM_RAKUTEN'] = null;
            jsonEncode(1);
        }

    }

    static function checkExistedApplePayOrder()
    {
        $data = Globals::session('INFO_RAKUTEN');

        if (!empty($data) && !empty($data['pay_type']) && $data['pay_type'] == 'apple_pay') {
            $logs = ['function' => 'checkExistedApplePayOrder', 'status' => 'true'];

            self::log_message(array_merge($logs, $data));
            jsonEncode(['status' => true]);
        }

        jsonEncode(['status' => false]);
    }

    static function log_message($log_msgs)
    {
        if (isset($log_msgs['AccessPass'])) {
            unset($log_msgs['AccessPass']);
        }

        $log_msgs = array_merge(['user_agent' => $_SERVER['HTTP_USER_AGENT']], $log_msgs);
        $log_msg = "\n------------------- " . date('Y-m-d H:i:s') . " -----------------------";

        foreach ($log_msgs as $key => $value) {
            $log_msg .= "\n" . $key . ': ' . $value;
        }

        $log_file = "template/cached_files/apple_pay.log";

        $log_msg .= "\n------------------------------------------";

        file_put_contents($log_file, $log_msg, FILE_APPEND);
    }

    private static function _minifyFiles()
    {
        $files = [
            'css' => [
//                'common/smart/design/css/market.css',
//                'common/smart/design/css/business_customer.css',
//                'common/smart/design/css/delivery-redesign.css',
//                'common/smart/design/css/guide.css',
//                'common/smart/design/css/jquality.css',
//                'common/smart/design/css/all.css',
//                'common/design/user/css/sp-item-info.css',
//                'common/smart/design/css/item_detail_new_list_sp.css',
//                'common/smart/design/css/printing_factory_smart.css',
//                'common/css/atjam.css',
//                'common/css/sp/files/battle.css',
//                'common/css/pc/files/battle.css',
            ],
            'js' => [
                //'common/lib/jquery/jquery.tagcloud.js',
                //'common/smart/design/js/smartphone.js',
                //'common/smart/design/js/jquery.biggerlink.js',
                //'common/smart/design/js/main.js',
                //'common/design/user/js/search.base.js',
                //'common/design/user/js/list_review.js',
                //'common/js/sp_common.js',
                //'common/smart/design/js/tabs.js',
            ]
        ];

        foreach ($files as $type => $paths) {
            foreach ($paths as $path)
            {
                self::_minify([$path], $type, sprintf('%s.min.%s', explode(sprintf('.%s', $type), $path)[0], $type));
            }
        }
    }

    static function getBottomButton()
    {
        jsonEncode(ccDraw::drawBottomButton(Globals::get('page')));
    }

    static function directUrlPayment()
    {
        $url = sprintf('%s/regist.php?type=pay', ApiConfig::DOMAIN);

        if (!empty($_POST['PayPayTrackingID'])) {
            $url = sprintf('%s&back=true', $url);
        }

        HttpUtil::postLocation($url, $_POST);
    }

    static function countUserSendMail()
    {
        global $sql;
        $rawQuery = '';

        (empty(Globals::post('start_M'))) ? $start_M = 0 : $start_M = Globals::post('start_M');
        (empty(Globals::post('start_D'))) ? $start_D = 0 : $start_D = Globals::post('start_D');
        (empty(Globals::post('start_Y'))) ? $start_Y = 0 : $start_Y = Globals::post('start_Y');
        $start = @mktime(0, 0, 0, $start_M, $start_D, $start_Y);

        (empty(Globals::post('end_M'))) ? $end_M = date('m') : $end_M = Globals::post('end_M');
        (empty(Globals::post('end_D'))) ? $end_D = date('d') : $end_D = Globals::post('end_D');
        (empty(Globals::post('end_Y'))) ? $end_Y = date('Y') : $end_Y = Globals::post('end_Y');
        $end = @mktime(0, 0, 0, $end_M, $end_D, $end_Y);

        if (Globals::post('mail_recipient') == 0) {
            $rawQuery = sprintf('SELECT
                                        `user`.id 
                                    FROM
                                        `user`
                                    WHERE
                                         `user`.regist_unix > 0 
                                        AND `user`.regist_unix >= %s 
                                        AND `user`.regist_unix <= %s 
                                        AND `user`.state = 1 
                                    GROUP BY
                                        `user`.id 
                                    ORDER BY
                                        `user`.id', $start, $end);
        } elseif (Globals::post('mail_recipient') == 1) {
            $rawQuery = sprintf('SELECT
                                            `user`.id 
                                        FROM
                                            `user` 
                                        WHERE
                                            `user`.regist_unix > 0 
                                            AND `user`.regist_unix >= %s 
                                            AND `user`.regist_unix <= %s
                                            AND `user`.state = 1 
                                            AND `user`.mail_magazine.state = 1
                                        ORDER BY
                                            `user`.id', $start, $end);
        }

        $result = $sql->rawQuery($rawQuery);
        $count = $result->num_rows;
        jsonEncode(['count' => $count]);
    }

    static function cancelStepMail()
    {
        global $sql;
        $data = array();

        if (empty($_GET['user'])) {
            $data['noti'] = "";
        }

        if (!isset($_GET['status'])) {
            $data['noti'] = "";
        } else {
            if ($_GET['status'] != 1 && $_GET['status'] != 0) {
                $data['noti'] = "";
            }
        }

        if (!isset($data['noti'])) {
            $query = sprintf('SELECT id FROM user WHERE MD5(id) = \'%s\' LIMIT 1', $_GET['user']);
            $user = $sql->sql_fetch_assoc($sql->rawQuery($query));
            //cancel step mail
            $rec = $sql->setData('user', null, 'state_step_mail', $_GET['status']);
            $sql->updateRecord('user', $rec, $user['id']);
            $data['noti'] = "OK";
        }
        jsonEncode($data);
    }
    static function getRecommen1Item()
    {
        $data = Extension::getRecommen4Item();
        print $data;
    }

    static function getInfoRecommen1Item()
    {
        global $sql;
        $recommenitemmodalhtml = '';
        $item_type_re = Globals::get('item_type');
        $buy_item_blank = '';
        $result = countBlankItem($item_type_re);
        if (!$result) {
            $buy_item_blank = "<a style='background: #747474; color: #000000; cursor: unset;' class='design_btn right' disabled='true'>無地で購入する</a>";
        } else {
            $buy_item_blank = "<a class='design_btn right' href='/proc.php?run=createBlankItem&type=blank_prod&id=" . $item_type_re . "'>無地で購入する</a>";
        }

        $optionItem = '<option value="0">デザイン選択</option>';

        if (Globals::session("LOGIN_TYPE") == 'user') {

            $tmp_wheresearch = $sql->setWhere('item', null, "image_id", "!=", '');
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "image_id", "!=", null);
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "owner_item", "=", '');
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "owner_item", "=", null);
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "owner_item", "=", null);
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "owner_item", "=", null);
            $tmp_wheresearch = $sql->setWhere('item', $tmp_wheresearch, "user", "=", Globals::session("LOGIN_ID"));
            $Order_by = $sql->setOrder('item', null, 'regist_unix', 'DESC');
            $result = $sql->getSelectResult('item', $tmp_wheresearch, $Order_by);

            if ($result->num_rows >= 1) {

                while ($sub_result = $sql->sql_fetch_assoc($result)) {
                    $optionItem .= '<option data_id="' . $sub_result["image_id"] . '" value="' . $sub_result['id'] . '">' . $sub_result['name'] . '</option>';
                }
            } else {
                $optionItem = '<option value="0">デザインがありません。</option>';
            }
        } else {
            $cart = Globals::session("CART_ITEM");
            foreach ($cart as $tmp_item) {
                $optionItem .= '<option data_id="' . $tmp_item["image_id"] . '" value="' . $tmp_item["image_id"] . '">' . $tmp_item["image_design_change_name"] . '</option>';
            }
        }

        $titleText = '';
        $tmp_where_head_text = $sql->setWhere('master_item_type_page', null, "item_type", "=", $item_type_re);
        $result_text_head = $sql->getSelectResult('master_item_type_page', $tmp_where_head_text);

        while ($sub_result_head_text = $sql->sql_fetch_assoc($result_text_head)) {
            $titleText = $sub_result_head_text["item_text"];
        }

        $getside = getItemType($item_type_re);
        $titel = Extension::drawListSides(null, $getside);

        if ($getside) {
            $recommenitemmodalhtml .= '<div class="modal-content">
		<span  class="close">
		    <span class="close-modal-cart"></span>
        </span>
		<div class="top-modal-item">
			<div style="text-align: center; height: 200px; max-width: 50%;padding-right: 10px;" class="left-modal-item" >
				<img class="image_preview_itemre" src="' . $getside["preview_url"] . '">
			</div>
			<div style="width: 50%" class="right-modal-item">
				<div class="top-recomment-item right-top-recomment-item" ><p>' . $getside["name"] . '</p><p>' . $getside["item_code_nominal"] . ' | ' . $getside["maker"] . '</p></div>
				<div class="two-span-button">
					<span>' . $getside["color_total"] . '</span>
					<span>' . $getside["size"] . '</span>
				</div>
				<div class="number-side-in">
					印刷可能箇所</br>' . $titel . '
				</div>
				<p class="end_p"><label class="first_price">価格</label><label class="end_label end_label_modalRe">' . number_format($getside["tool_price"]) . '円~</label></p>
			</div>
		</div>
		<p class="info-modal-cart">
			'.$titleText.'
		</p>
		<div class="bottom-modal-item">
			<div class="select-item-recoment">
				<select class="select-same-design">
				' . $optionItem . '
				</select>
			</div>
			<div class="buy-item-recomment">
				<a data-id="' . $item_type_re . '" type="button" class="design_again_recomment_item">上記デザインで作成</a>
				' . $buy_item_blank . '

			</div>

		</div>
	</div>';
        }

        print $recommenitemmodalhtml;
    }

    static function getInfoItemSelect()
    {
        global $sql;
        $data = array();
        $item_type_re = Globals::get('item_id');
        if ($item_type_re) {
            $recItem = $sql->selectRecord("item", $item_type_re);
            if ($recItem) {
                $data["price"] = number_format($recItem["price"]);
                if (!empty($recItem["item_preview1"])) {
                    $data["image_item"] = $recItem["item_preview1"];
                    return jsonEncode($data);
                } else {
                    if (!empty($recItem["item_preview2"])) {
                        $data["image_item"] = $recItem["item_preview2"];
                        return jsonEncode($data);
                    } else {
                        if (!empty($recItem["item_preview3"])) {
                            $data["image_item"] = $recItem["item_preview3"];
                            return jsonEncode($data);
                        } else {
                            if (!empty($recItem["item_preview4"])) {
                                $data["image_item"] = $recItem["item_preview4"];
                                return jsonEncode($data);
                            }
                        }
                    }
                }
            } else {
                $cart = Globals::session("CART_ITEM");
                foreach ($cart as $tmp_item) {
                    if ($item_type_re == $tmp_item["image_id"]) {
                        $data["price"] = number_format($tmp_item["item_price"]);
                        if (!empty($tmp_item["image_preview1"])) {
                            $data["image_item"] = $tmp_item["image_preview1"];
                            return jsonEncode($data);
                        } else {
                            if (!empty($tmp_item["image_preview2"])) {
                                $data["image_item"] = $tmp_item["image_preview2"];
                                return jsonEncode($data);
                            } else {
                                if (!empty($tmp_item["image_preview3"])) {
                                    $data["image_item"] = $tmp_item["image_preview3"];
                                    return jsonEncode($data);
                                } else {
                                    if (!empty($tmp_item["image_preview4"])) {
                                        $data["image_item"] = $tmp_item["image_preview4"];
                                        return jsonEncode($data);
                                    }
                                }
                            }
                        }

                    }
                }

            }
        } else {
            return null;
        }
    }

    static function getDrawToolLinkChange()
    {

        $image_id = Globals::get('image_id');
        $color_id = Globals::get('color_id');
        $modal_id = Globals::get('modal_id');

        $url = Extension::getDrawToolLinkString($image_id, null, null, $modal_id, $color_id, null, null, $changeModaltype = true);

        jsonEncode($url);


    }
    /**
     * Get Seller Reward
     */
    static function getSellerReward() {
        global $sql;
        $item_id = Globals::get("item");
        $data['fee_user'] = Globals::get("fee_user");

        $msg = '';

        $i_rec = $sql->selectRecord("item",$item_id);
        if($i_rec["owner_item"])
        {
            if(!$tmp_i_rec = $sql->selectRecord("item", $i_rec["owner_item"])) $msg = "<li>創作元の一次商品が見つかりません。</li>\n";
        }
        else
        {
            if(!CheckUtil::is_check($data, "fee_user")) $msg .= "<li>デザイン料（販売者報酬）を入力してください。</li>\n";
            else if(!is_numeric($data['fee_user'])) $msg .= "<li>デザイン料（販売者報酬）を数値で入力してください。</li>\n";
            else if($data['fee_user'] < 0 || $data['fee_user'] > 50000) $msg .= "<li>デザイン料（販売者報酬）は0円～50000円の範囲で入力してください。</li>\n";
        }

        if ($msg) {
            jsonEncode(array('status' => 400, 'msg' => $msg));
        } else {
            $userDesignItem = $sql->selectRecord('user', $i_rec["user"]);
            $system_usage_fee = 10;
            if(!empty($userDesignItem["system_usage_fee"]) || $userDesignItem["system_usage_fee"]== '0'){
                $system_usage_fee = $userDesignItem["system_usage_fee"];
            }

            $result = round($data["fee_user"] / 100 * (100-$system_usage_fee) );
            if (Globals::get('original')) {

                $item_price_original = getToolItemPrice($i_rec, $i_rec['item_type'], $i_rec['item_type_sub']);
                $fee_user_original = $i_rec['fee_user_original'];
                if (empty($fee_user_original)) {
                    $fee_user_original = getFreeUserOriginal($item_price_original, $data);
                }

                $price = $item_price_original + $fee_user_original + $i_rec['fee_owner'] + $i_rec['fee_option'] + ($data["fee_user"] - $fee_user_original);
            } else {
                $price = $i_rec['price'] + ($data["fee_user"] - $i_rec['fee_user']);
            }
            jsonEncode(array('status' => 200, 'msg' => $msg, 'result' => $result .'円', 'price' => $price.'円'));
        }
    }

    static function getHtmlRecoment4Item() {

        print Extension::getRecommen4Item();

    }
    static function getHtmlRecoment8Item() {

        print Extension::getRecommen8Item();

    }

    static function getItemDetail()
    {
        global $sql;

        $item = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT item_text, maker, item_code_nominal, material, material_text, thickness, weight, print_method, size_text, purchase_note FROM item JOIN master_item_type ON master_item_type.id = item.item_type WHERE item.id = "%s"', Globals::get('item'))));
        $types = [
            'maker' => [
                'title' => 'メーカー',
                'conjunction' => '&nbsp;',
                'content' => ['maker', 'item_code_nominal']
            ],
            'material' => [
                'title' => '素材',
                'conjunction' => '<br>',
                'content' => ['material', 'material_text']
            ],
            'thickness' => [
                'title' => '生地の厚さ',
                'conjunction' => '&nbsp;',
                'content' => ['thickness']
            ],
            'weight' => [
                'title' => '重さ',
                'conjunction' => '&nbsp;',
                'content' => ['weight']
            ],
            'print_method' => [
                'title' => '印刷手法',
                'conjunction' => '&nbsp;',
                'content' => ['print_method']
            ],
            'size_text' => [
                'title' => 'サイズ',
                'conjunction' => '&nbsp;',
                'content' => ['size_text']
            ],
            'purchase_note' => [
                'title' => '注意事項',
                'conjunction' => '&nbsp;',
                'content' => ['purchase_note']
            ],
        ];

        $item_detail = '';
        foreach ($types as $field => $data) {
            if (!empty($item[$field])) {
                $item_detail .= sprintf('<div class="product-detail-title">%s</div>', $data['title']);
                $item_detail .= sprintf('<div class="product-detail-col">');
                foreach ($data['content'] as $key => $value) {
                    if ($key == 0) {
                        $conjunction = '';
                    } else {
                        $conjunction = $data['conjunction'];
                    }

                    $item_detail .= $conjunction . $item[$value];
                }
                $item_detail .= sprintf('</div>');
            }
        }

        echo sprintf('<div id="item-detail">
                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active link-item-info" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">商品詳細</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="clearfix"></div>
                                        <div class="tab-content mt-5 mb-5" id="myTabContent">
                                            <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
                                                <p>%s</p>
                                                %s
                                            </div>
                                        </div>', $item['item_text'], $item_detail);
        exit();
    }

    static function changeCartThank()
    {
        global $sql;
        $data = array();
        $id = Globals::get('id');
        $status = Globals::get('status');

        if (!isset($id) || (!isset($status) && !in_array($status, [0, 1]))) {
            $data['status'] = 400;
        } else {
            $update = $sql->setData('pay', null, 'card_thank', $status);
            $sql->updateRecord('pay', $update, $id);
            $data['status'] = 200;
        }

        jsonEncode($data);
    }

    /**
     * update state send magazine mail
     */
    static function cancelMagazineMail()
    {
        global $sql;
        $data = array();

        if (empty($_GET['user'])) {
            $data['noti'] = "";
        }

        if (!isset($_GET['status'])) {
            $data['noti'] = "";
        } else {
            if ($_GET['status'] != 1 && $_GET['status'] != 0) {
                $data['noti'] = "";
            }
        }

        if (!isset($data['noti'])) {
            $query = sprintf('SELECT id FROM user WHERE MD5(id) = \'%s\' LIMIT 1', $_GET['user']);
            $user = $sql->sql_fetch_assoc($sql->rawQuery($query));
            //cancel magazine mail
            $rec = $sql->setData('user', null, 'mail_single_state', $_GET['status']);
            $sql->updateRecord('user', $rec, $user['id']);
            $data['noti'] = "OK";
        }
        jsonEncode($data);
    }

    /**
     * calculate total price item blank
     */
    static function calculateTotalPrice()
    {
        global $sql;
        $table = 'master_blank_item_price';
        $total_row = 0;
        $total_price = 0;
        $items = Globals::session('LIST_ITEM_BLANK');
        $item_row = Globals::get('item_row');

        if(empty($items)){
            if($item_row <= 0){
                $data['response'] = 400;
                $data = json_encode($data);
                print $data;
                exit;
            }
        }
        $where = $sql->setWhere($table, null, 'item_type', '=', Globals::get('item'));
        $where = $sql->setWhere($table, $where, 'item_type_sub', '=', Globals::get('sub'));
        $where = $sql->setWhere($table, $where, 'item_type_size', '=', Globals::get('size'));

        $rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

        if ($item_row > 0) {
            $items[$rec['item_type']][$rec['id']]['row'] = Globals::get('item_row');
            $items[$rec['item_type']][$rec['id']]['price'] = $rec['price'] * Globals::get('item_row');
        } else {
            if (isset($items[$rec['item_type']][$rec['id']])) {
                unset($items[$rec['item_type']][$rec['id']]);
            }

        }

        foreach ($items[$rec['item_type']] as $key => $item) {
            $total_row += $item['row'];
            $total_price += $item['price'];
        }

        $items['total_row'] = $total_row;
        $items['total_price'] = $total_price;

        Globals::setSession('LIST_ITEM_BLANK', $items);

        $total_price_item = 0;
        if(!empty($items[$rec['item_type']][$rec['id']]['price'])){
            $total_price_item = number_format($items[$rec['item_type']][$rec['id']]['price']);
        }

        $data = [
            'response' => 200,
            'id' => $rec['id'],
            'total_row' => $items['total_row'],
            'total_price' => number_format($items['total_price']) . '円',
            'total_price_tax' => number_format($items['total_price'] * 1.1) . '円(税込)',
            'total_price_item' => $total_price_item . '円'
        ];

        $data = json_encode($data);
        print $data;
        exit;
    }

    /**
     * add cart item blank item blank
     */
    static function addCartItemBlank()
    {
        global $sql;
        $data['response'] = 200;
        if (empty(Globals::session('LIST_ITEM_BLANK')[Globals::post('item')])) {
            $data['response'] = 404;
        } else {
            set_cart_type(false);
            $items = Globals::session('LIST_ITEM_BLANK')[Globals::post('item')];
            $list_item_sub = array();
            foreach ($items as $key => $value) {

                $rec = $sql->selectRecord('master_blank_item_price', $key);
                $item_type = $sql->selectRecord('master_item_type', Globals::post('item'));
                $preview_image = $sql->selectRecord('master_item_type_sub', $rec['item_type_sub'])['thumbnail_url'];

                //create item blank
                $table = 'item';
                $where = $sql->setWhere($table, null, "name", "=", $item_type['name']);
                $where = $sql->setWhere($table, $where, "product_type", "=", 'bl');
                $item = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

                if (empty($item)) {

                    $id = SystemUtil::getUniqId($table, false, true);
                    $regist = $sql->setData($table, null, "id", $id);
                    $regist = $sql->setData($table, $regist, "name", $item_type['name']);
                    $regist = $sql->setData($table, $regist, "item_type", $rec['item_type']);
                    $regist = $sql->setData($table, $regist, "item_type_sub", $rec['item_type_sub']);
                    $regist = $sql->setData($table, $regist, "item_type_size", $rec['item_type_size']);
                    $regist = $sql->setData($table, $regist, "item_text", $item_type['item_name']);
                    $regist = $sql->setData($table, $regist, "item_preview1", $preview_image);
                    $regist = $sql->setData($table, $regist, "product_type", 'bl');
                    $regist = $sql->setData($table, $regist, "state", 1);
                    $regist = $sql->setData($table, $regist, "regist_unix", time());

                    $item = $sql->addRecord($table, $regist);
                }

                appliMultipleItemBlank($item, $rec['item_type'], $rec['item_type_sub'], $rec['item_type_size'], $rec['price'], $value['row'], $preview_image, $list_item_sub);
                $list_item_sub[$rec['item_type_sub']] = 1;

            }

        }
        Globals::setSession('LIST_ITEM_BLANK','');
        $data = json_encode($data);
        print $data;
        exit;
    }

    static function add_mask()
    {
        global $sql;
        $rec = constants::MASK;
        $url = '/page.php?p=3rd-mask';
        $has_current_mask = false;
        Globals::setSession('IS_MASK', true);
        Globals::setSession('IS_PERIODIC', false);

        if (Globals::get('id') === constants::PERIODIC_MASK['item_id']) {
            $rec = constants::PERIODIC_MASK;
            Globals::setSession('IS_PERIODIC', true);
        }

        if (Globals::get('id') == $rec['item_id']) {
            $url = '/page.php?p=cart';
            $item = $sql->selectRecord('item', $rec['item_id']);
            $quantity = Globals::get('quantity');
            $cart = Globals::session("CART_ITEM");

            if (empty($quantity)) {
                $quantity = 1;
            }

            if (!empty(Globals::session('OTHER_CART_ITEM'))) {
                $cart = Globals::session('OTHER_CART_ITEM') + $cart;
                Globals::setSession('OTHER_CART_ITEM', null);
            }

            if($cart) {
                foreach($cart as $key => $val) {
                    if ($val['item_id'] == $rec['item_id']) {
                        $has_current_mask = true;

                        foreach ($val['item_type_size_detail'] as $size_key => $size) {
                            $cart[$key]['item_type_size_detail'][$size_key]['total'] += $quantity;
                        }
                    }
                }

                Globals::setSession('CART_ITEM', $cart);
            }

            if (!$has_current_mask) {
                appliMultipleItemBlank($item, $rec['item_type'], $rec['item_type_sub'], $rec['item_type_size'], constants::MASK_PRICE, $quantity, $item['item_preview1'], []);
            }
        }

        refreshCart();
        HttpUtil::location($url);
    }

    /**
     * search info order
     */
    static function getInfoOrder()
    {
        global $sql;
        global $TRANSPARENT_BACKGROUND_ITEM_ID;
        global $FREE_DESIGN_ITEM_ID;
        global $NOBORI;
        $list_pay_state = ["未決済", "決済完了", "キャンセル"];
        $list_delivery_state = ["注文受付", "配送済み", "キャンセル"];
        $list_state_after2 = [
            0 => '取引登録待ち',
            10 => '審査中',
            11 => '出荷報告待ち',
            12 => '与信審査NG',
            13 => '与信審査保留',
            2 => 'NG',
            3 => 'エラー',
            4 => '出荷報告OK',
            5 => '取引キャンセル'
        ];
        $data['response'] = 200;

        if (empty($_GET['pay_num'])) {

            $data['response'] = 404;
            $data['message'] = '<span style="color: #ff0000">注文番号を入力してください。</span>';
        } else {

            $pay_num = $_GET['pay_num'];
            $note = '';

            $order = $sql->keySelectRecord('pay', 'pay_num', $_GET['pay_num']);

            if (empty($order)) {

                $data['response'] = 404;
                $data['message'] = '<span style="color: #ff0000">検索した注文が見つかりません。</span><br>
                                    <span style="color: #ff0000">ご注文番号を再度確認してください。</span>';

            } else {
                if ($order['regist_unix'] < time() - 30 * 24 * 3600) {
                    $data['response'] = 404;
                    $data['message'] = '<span style="color: #ff0000">確認期限は注文から1か月以内です。</span><br>
                                        <span style="color: #ff0000">それ以前の注文履歴はマイページから確認ください。</span>';
                } else {
                    $pay_state = $list_pay_state[$order['pay_state']];
                    if ($order['pay_type'] == 'after2') {
                        $after_log = $sql->keySelectRecord('after_log2', 'pay_id', $order['id']);
                        if ($after_log['state'] == 2 || $after_log['state'] == 3 || $after_log['state'] == 12 || $after_log['state'] == 13) {
                            $pay_state = $list_state_after2[$after_log['state']];
                        }
                    }
                    $delivery_state = $list_delivery_state[$order['delivery_state']];

                    if ($order['pay_state'] == 0) {

                        $send_datetime = '未定';
                        if ($order['pay_type'] == 'after2' && ($pay_state == 'NG' || $pay_state == '与信審査NG' || $pay_state == 'エラー' || $pay_state == '与信審査保留')) {
                            $note = '<br><span style="color: #ff0000">与信審査結果に問題がございます。</span>
                                     <br><span style="color: #ff0000">メールを送信しておりますのでご確認ください。</span>
                                     <br><span style="color: #ff0000">メールが届いていない場合は直接お問い合わせください。</span>';
                        } else {
                            $note = '<br><span style="color: #ff0000">ご入金後に発送予定日が表示されます。</span>';
                        }
                    } elseif ($order['pay_state'] == 2) {


                        $send_datetime = '未定';
                        $note = '<br><span style="color: #ff0000">デザイン制作・透過サービスをお申し込みの場合はデザイン確定後に進行用の注文番号が発行されます。</span><br>
                                    <span style="color: #ff0000">詳しくはオペレーターへご連絡下さい。</span>';
                    } else {

                        if ($order['delivery_state'] == 0) {
                            if (!empty($order['printty_export']) && $order['printty_export'] == 'exported') {

                                $url = sprintf('http://api.beta.printty.maruig.com/external/order/status?pay_num=%s', $_GET['pay_num']);
                                $client = new \GuzzleHttp\Client();

                                try {
                                    $response = $client->request('GET', $url);
                                } catch (Exception $exception) {
                                    print_r($exception->getMessage());
                                    die;
                                }

                                $val = json_decode($response->getBody()->getContents(), true);


                                $send_datetime = date("Y年m月d日", strtotime($val['status']['Order']['production_date_preferred'])) . '発送予定';

                                if ($order['garment_state'] == 1) {

                                    $note = '<br><span style="color: #ff0000">交通トラブル・年末年始・GW等長期休暇による遅れが発生する場合があります。</span>';
                                } else {
                                    $note = '<br><span style="color: #ff0000">まだ確定ではございません。</span><br><span style="color: #ff0000">※交通トラブル・年末年始・GW等長期休暇による遅れが発生する場合があります。</span>';
                                }
                            } else {
                                $delivery_state = '生産調整中';
                                $time = date("Y-m-d H:i", $order['regist_unix']);

                                // fetch item same day
                                $item_same_day = same_day_master_item_type();
                                $flag_same_day = true;
                                // fetch item from order
                                $clume = $sql->setClume('pay_item', null, 'item_type');
                                $clume = $sql->setClume('pay_item', $clume, 'item');
                                $where = $sql->setWhere('pay_item', null, 'pay', '=', $order['id']);
                                $result = $sql->getSelectResult('pay_item', $where, null, null, $clume);

                                $display_note = false;
                                $list_item_type = array();
                                while ($rec = $sql->sql_fetch_assoc($result)) {

                                    if (in_array($rec['item_type'], $item_same_day) && !in_array("same_day", $list_item_type)) {
                                        $list_item_type[] = 'same_day';
                                    } elseif (!in_array("nobori", $list_item_type) && in_array($rec['item_type'], $NOBORI)) {
                                        $list_item_type[] = 'nobori';
                                    } else {
                                        $list_item_type[] = 'normal';
                                    }

                                    if ($display_note == false) {
                                        if ($rec["item"] == $FREE_DESIGN_ITEM_ID || $rec["item"] == $TRANSPARENT_BACKGROUND_ITEM_ID) {
                                            $display_note = true;
                                        }
                                        if ($display_note == false) {
                                            $table2 = "item";
                                            $where2 = $sql->setWhere($table2, null, "id", "=", $rec["item"]);
                                            $result2 = $sql->getSelectResult($table2, $where2, null, null);
                                            while ($rec2 = $sql->sql_fetch_assoc($result2)) {
                                                if ($rec2["owner_item"] == $FREE_DESIGN_ITEM_ID || $rec2["owner_item"] == $TRANSPARENT_BACKGROUND_ITEM_ID) {
                                                    $display_note = true;
                                                }
                                            }
                                        }
                                    }
                                }

                                //calculate send date
                                if ($order['pending'] == 2) {
                                    $send_datetime = '発送日未定';
                                    $note = '<br><span style="color: #ff0000">確認事項がある場合、生産保留とさせていただいております。</span><br><span style="color: #ff0000">確認事項のメールが届いていない場合は直接お問い合わせください。</span>';
                                } else {
                                    $send_datetime = '';
                                    $note = '';

                                    if (in_array("same_day", $list_item_type)) {
                                        $send_datetime .= '<br>' . calc_senddate($time, false, false, true, true, "Y年n月j日")[1] . '発送で生産中[即日アイテム]';
                                    }

                                    if (in_array("normal", $list_item_type)) {
                                        $send_datetime .= '<br>' . calc_senddate($time, false, false, false, false, "Y年n月j日")[1] . '発送で生産中[通常アイテム]';
                                        if (in_array("nobori", $list_item_type)) {
                                            $note .= '<br><span style="color: #ff0000">通常商品は3営業日での別送となります。</span>';
                                        }
                                    }

                                    if (in_array("nobori", $list_item_type)) {
                                        $delivery_state = '要確認';
                                        $send_datetime .= '<br>' . calc_senddate($time, false, true, false, false, "Y年n月j日") . '発送で生産中[別納アイテム]';
                                        $note .= '<br><span style="color: #ff0000">のぼり類のご注文は5-6営業日での発送となります。詳しくは直接お問い合わせください。</span>';
                                    }

                                    if ($display_note == true && empty($note)) {
                                        $note = '<br><span style="color: #ff0000">デザイン制作・透過サービスをお申し込みの場合はデザイン定後に進行用の注文番号が発行されます。詳しくはオペレーターへご連絡下さい。</span>';
                                    }
                                }

                            }
                        } elseif ($order['delivery_state'] == 1) {
                            $delivery_service = '';
                            $send_datetime = date("Y年m月d日", strtotime($order['send_datetime'])) . '発送済み';
                            if ($order['delivery_service'] == 'yamato_marui') {
                                $delivery_service = 'ヤマト運輸';
                            } elseif ($order['delivery_service'] == 'sagawa_marui') {
                                $delivery_service = '佐川急便';
                            }

                            $note = sprintf('<br><span>配達会社：%s</span><br><span>伝票番号：%s</span>', $delivery_service, $order['tracking_number']);
                        } else {
                            $send_datetime = '未定';
                            $note = '<br><span style="color: #ff0000">デザイン制作・透過サービスをお申し込みの場合はデザイン確定後に進行用の注文番号が発行されます。</span>
                                     <br><span style="color: #ff0000">詳しくはオペレーターへご連絡下さい。</span>';
                        }
                    }
                    $data['message'] = sprintf('<span>注文番号：%s</span><br><span>決済状況：%s</span><br><span>配送状況：%s</span><br><span>発送予定日：%s</span>%s', $pay_num, $pay_state, $delivery_state, $send_datetime, $note);
                }
            }
        }
        $data = json_encode($data);
        print $data;
        exit;
    }

    static function prepareDownloadCsvWundou($data,$changegarment=false) {
        global $sql;

        $contents = '"JANコード","他品番","カラー名","サイズ名","販売数","お客様注文Ｎｏ"'."\n";

        while($rec = $sql->sql_fetch_assoc($data))
        {
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
            $rec = TextUtil::arrayReplace($rec, '"', '""');

            $select = "SELECT
                            pay.pay_num,
                            master_item_jancode.jan_code,
                            master_item_jancode.sub_name,
                            master_item_jancode.size_name,
                            master_item_jancode.mapping_code,
                            pay_item.item_row FROM pay_item ";
            $inner_join = 'INNER JOIN pay ON pay.id = pay_item.pay
                           INNER JOIN master_item_type ON master_item_type.id = pay_item.item_type
                           INNER JOIN master_item_type_size ON master_item_type_size.id = pay_item.item_type_size
                           INNER JOIN master_item_type_sub ON master_item_type_sub.id = pay_item.item_type_sub
                           INNER JOIN master_item_jancode ON master_item_jancode.mapping_code = master_item_type.vendor_item_code 
                           AND master_item_type_sub.item_code = master_item_jancode.sub_code 
                           AND master_item_type_size.item_code = master_item_jancode.size_code ';
            $where = sprintf('WHERE
                            pay_item.pay = "%s" 
                            AND master_item_type.maker = "%s"',$rec['id'],"WUNDOU");
            if(Globals::get('product-type') == 'blank') {
                $where .= sprintf(' AND master_item_type.product_type LIKE "blank" AND pay_item.item NOT IN ("%s","%s")', constants::PERIODIC_MASK['item_id'], constants::MASK['item_id']);
            }

            $query = $select . $inner_join .$where;

            $result = $sql->rawQuery($query);
            while ($tmp_rec = $sql->sql_fetch_assoc($result)) {
                if (empty($tmp_rec['jan_code']) || empty($tmp_rec['mapping_code']) || empty($tmp_rec['item_row'])) {
                    continue;
                }
                $contents .= '"' . $tmp_rec['jan_code'] . '","' . $tmp_rec['mapping_code'] . '","' . $tmp_rec['sub_name'] .
                    '","' . $tmp_rec['size_name'] . '","' . $tmp_rec['item_row'] . '","' . $tmp_rec['pay_num'] . '"';
                $contents .= "\n";

            }
        }

        return $contents;
    }

    static function prepareDownloadCsvTradeWorks($data,$changegarment=false) {
        global $sql;

        $contents = '"品番","数量","お届け先会社名","お届け先会社名カナ","部署名","お届け先名（姓）","お届け先名（名）","お届け先郵便番号","お届け先都道府県","お届け先住所1","お届け先住所2","お届け先電話番号","お客様注文番号","荷主名","荷主電話番号","備考"'."\n";

        while($rec = $sql->sql_fetch_assoc($data))
        {
            if($changegarment==true){
                $state = 1;
                changeGarment($rec["id"], $state);
            }
            $rec = TextUtil::arrayReplace($rec, '"', '""');

            $tmp_table = "pay_item";
            $tmp_where = $sql->setWhere($tmp_table, null, "pay", "LIKE", $rec["id"]);
            $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "item", "NOT IN", array(constants::PERIODIC_MASK['item_id'],constants::MASK['item_id']));
            if(Globals::get('product-type') == 'blank') {
                $tmp_where = $sql->setWhere($tmp_table, $tmp_where, "product_type", "LIKE", 'blank');
            }
            $tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
            $tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

            while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
            {
                $master_item_type = "";
                $master_item_type_size = "";
                $master_item_type_sub = "";

                if($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) $master_item_type = $tmp2_rec["vendor_item_code"];$vendor_id = $tmp2_rec["vendor_id"];$master_item = $tmp2_rec["id"];
                if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $master_item_type_size = $tmp2_rec["item_code"];
                if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $master_item_type_sub = $tmp2_rec["item_code"];
                if($vendor_id!=9){
                    continue;
                }
//              $bikou = "配送先氏名：".$rec["name"]."様\n".'注文番号：'.$rec["pay_num"];
//				$bikou = $rec["name"];
                $bikou = "";

                if ($master_item == 'IT432') {
                    $master_item_type = $master_item_type;
                }else {
                    $master_item_type = $master_item_type.'-'.$tmp2_rec["vendor_color_code"];
                }

                $contents .= '"'.$master_item_type.'","'.$tmp_rec["item_row"].'","丸井織物株式会社","マルイオリモノカブシキガイシャ","プリント課","プリント課","担当者","9291811","石川県","鹿島郡中能登町二宮タ部１６６番地","丸井織物株式会社","07012893701","","トレードワークス","0354687271","カプセルボックスオンデマンド分"';
                $contents .= "\n";
            }
        }

        return $contents;
    }

    static function findItemColor()
    {
        global $sql;
        $data = array();
        $tmp_item = '';
        $id = Globals::get('id');
        $img ='';

        $tmp_wheresearch = $sql->setWhere('master_item_type_sub', null, "item_type", "=", $id);
        $tmp_order = $sql->setOrder('master_item_type_sub', null, 'is_main', 'DESC');
        $result = $sql->getSelectResult('master_item_type_sub', $tmp_wheresearch);
        $i=0;

        if ($result->num_rows >= 1) {

            while ($sub_result = $sql->sql_fetch_assoc($result)) {
                $tmp_item .= '<i class="fa fa-square" data-color="'.$sub_result["color"].'" style="color: '.$sub_result["color"].'"></i>';
                $i++;
                if($i==1){
                    $img =  $sub_result["thumbnail_url"];
                }
            }
        }

        $pay = $sql->selectRecord('master_item_type', $id);
        if($pay){
            $data['size'] = $pay["size"];
            $data['item_price'] = $pay["item_price"];
        }

        $tmp_where_item = $sql->setWhere('master_item_type_page', null, "item_type", "=", $id);
        $result1 = $sql->getSelectResult('master_item_type_page', $tmp_where_item);
        if ($result1->num_rows >= 1) {

            while ($sub_result1 = $sql->sql_fetch_assoc($result1)) {
                $data['item_des']= $sub_result1["item_text_detail"];
                $data['material_text']= $sub_result1["material_template"];
            }
        }
        $data['item'] = $tmp_item;
        $data['count']= $i;
        $data['thumbnail_url']= $img;
        $data['sent_date']= self::getSendDateItemSameDayComepare($id);
        $data['rating']= ccDraw::drawStarReviewItem(['id' => $id, 'compare' => true]);
        $data['sitePrint']= getSideCompre($id);
        jsonEncode($data);
    }

    static function getSendDateItemSameDayComepare($itemType)
    {
        global $sql;
        $send_date = getCachedContent('content', 'send_date', false);

        $itemTypeget = $sql->selectRecord('master_item_type', $itemType);
        $sameday = false;
        $checknobori = false;
        if($itemTypeget){
            if($itemTypeget["flag_same_day"]==1){
                $sameday = true;
            }
        }
        if($itemType=='IT303' || $itemType=='IT304'
            || $itemType=='IT305'  || $itemType=='IT306'
            || $itemType=='IT307'  || $itemType=='IT308'
            || $itemType=='IT309' || $itemType=='IT310'
            || $itemType=='IT311'  || $itemType=='IT312'  || $itemType=='IT313'
            || $itemType=='IT314'  || $itemType=='IT315'  || $itemType=='IT316'
            || $itemType=='IT317'  || $itemType=='IT318'  || $itemType=='IT319' || $itemType=='IT320'
            || $itemType=='IT321'  || $itemType=='IT322'  || $itemType=='IT323'
            || $itemType=='IT324'  || $itemType=='IT325'  || $itemType=='IT326'
            || $itemType=='IT327'  || $itemType=='IT328'  || $itemType=='IT329'
            || $itemType=='IT330' || $itemType=='IT331'
            || $itemType=='IT442' || $itemType=='IT443' || $itemType=='IT444' || $itemType=='IT445'
            || $itemType=='IT446' || $itemType=='IT447' || $itemType=='IT448' || $itemType=='IT449'
            || $itemType=='IT450' || $itemType=='IT451' || $itemType=='IT452' || $itemType=='IT453'
            || $itemType=='IT454' || $itemType=='IT455' || $itemType=='IT456' || $itemType=='IT457'
            || $itemType=='IT458' || $itemType=='IT459' || $itemType=='IT460' || $itemType=='IT461'
            || $itemType=='IT462' ){
            $checknobori=true;
        }

        $date = new DateTime("now", new DateTimeZone('Asia/Tokyo'));
        $time_now = $date->format('H:i');
        $send_date = json_decode($send_date, true);

        if($sameday== true){
            if ($time_now < "09:00") {
                $send_date['receive_date'] = $send_date['order_date'];
            } else {
                $send_date['receive_date'] = $send_date['receive_date_fast'];
            }

            return $send_date['receive_date_fast'];
        }
        elseif ($checknobori==true){
            return $send_date['receive_date_nobori'];
        }
        else{
            return $send_date['receive_date'];
        }
        return $send_date['receive_date'];
    }

    static function get3ItemBestBy()
    {
        global $sql;
        $data = array();

        $id_check = Globals::get('status');
        $categoryid = Globals::get('categoryid');
        if(empty($categoryid)){
            $query = 'select SUM(item_row) AS total , item_type FROM pay_item  GROUP BY item_type ORDER BY total DESC LIMIT '.$id_check.'';
        }
        else{
            $query = 'select SUM(item_row) AS total , pay_item.item_type FROM pay_item  INNER JOIN item_web_categories ON item_web_categories.item_type = pay_item.item_type WHERE item_web_categories.category ='.$categoryid.' GROUP BY pay_item.item_type ORDER BY total DESC LIMIT '.$id_check.'';
        }
        $result = $sql->rawQuery($query);
        $i=0;
        if($result->num_rows>=1){
            while ($sub_result = $sql->sql_fetch_assoc($result)) {
                if($i==0){
                    $data["item1"] = $sub_result["item_type"];
                }
                if($i==1){
                    $data["item2"] = $sub_result["item_type"];
                }
                if($i==2){
                    $data["item3"] = $sub_result["item_type"];
                }
                $i++;
            }

        }
        else{
            $query = 'select * from item_web_categories WHERE category ='.$categoryid.' LIMIT '.$id_check.'';
            $result = $sql->rawQuery($query);
            while ($sub_result = $sql->sql_fetch_assoc($result)) {
                if($i==0){
                    $data["item1"] = $sub_result["item_type"];
                }
                if($i==1){
                    $data["item2"] = $sub_result["item_type"];
                }
                if($i==2){
                    $data["item3"] = $sub_result["item_type"];
                }
                $i++;
            }
        }
        jsonEncode($data);
    }
    static function findItemWebCategoryCompare()
    {
        global $sql;
        $data = array();
        $tmp_sub = '';
        $tmp_item = '';
        $id = Globals::get('id');
        $type = Globals::get('type');

        $where = $sql->setWhere('master_item_type', null, 'category_id', '=', $id);
        $where = $sql->setWhere('master_item_type', $where, 'state', '=', 1);

        $result = $sql->getSelectResult('master_item_type', $where);

        $tmp_item .= '<option value="" selected>選択してください</option>';

        while ($rec = $sql->sql_fetch_assoc($result)) {

            $tmp_item .= sprintf('<option value="%s">%s</option>', $rec['id'], $rec['name']);

        }

        $data['item'] = $tmp_item;
        jsonEncode($data);
    }



    static function search_faq_hot()
    {
        $data=array();

        $textsearch = Globals::get('text_search');
        $data['faq_re']= Extension::drawMultiQA(null, $textsearch);
        jsonEncode($data);
    }

    static function search_faq_hot2()
    {
        $data=array();

        $textsearch = Globals::get('text_search');
        $data['faq_re']= Extension::drawMultiFQA(null, $textsearch);
        jsonEncode($data);
    }
    static function commentItem()
    {
        global $sql;

        $data = Globals::post();
        $response = ['success' => true, ['fail' => []]];

        if (empty(trim($data['nickname']))) {
            $response['success'] = false;
            $response['fail']['nickname'] = true;
        } elseif (strlen(trim($data['nickname'])) > 255) {
            $data['nickname'] = substr(trim($data['nickname']), 0, 255);
        }

        if (empty(trim($data['comment']))) {
            $response['success'] = false;
            $response['fail']['comment'] = true;
        } elseif (strlen(trim($data['nickname'])) > 65535) {
            $data['nickname'] = substr(trim($data['nickname']), 0, 65535);
        }

        if ($response['success']) {
            $table = 'item_comments';
            $item_comment = $sql->setData($table, null, 'item_id', $data['item_id']);
            $item_comment = $sql->setData($table, $item_comment, 'user_id', Globals::session("LOGIN_ID"));
            $item_comment = $sql->setData($table, $item_comment, 'nickname', $data['nickname']);
            $item_comment = $sql->setData($table, $item_comment, 'comment', $data['comment']);
            $item_comment = $sql->setData($table, $item_comment, 'status', 0);
            $item_comment = $sql->setData($table, $item_comment, 'created_at', date('Y-m-d H:i:s'));
            $item_comment = $sql->setData($table, $item_comment, 'updated_at', date('Y-m-d H:i:s'));

            $sql->addRecord($table, $item_comment);
        }

        jsonEncode($response);
    }

    static function approveBattles()
    {
        $data["state"] = -1;

        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        if (!empty(Globals::get('id'))) {
            updateBattleStatus(Globals::get('id'), $state);
            $data["state"] = $state;

        } else {
            if(!$list = Globals::get("list")) jsonEncode($data);

            $list = explode("/", $list);
            $count_list = count($list);

            for($i = 0; $i < $count_list; $i++)
            {
                updateBattleStatus($list[$i], $state);
            }

            $data["state"] = $state;
        }

        jsonEncode($data);
    }

    static function loadMoreItemComments()
    {
        global $sql;

        $result = [
            'current' => 0,
            'comment' => '',
        ];

        $content = '';
        $per_page = 5;
        $current = Globals::get('current');
        $item_id = Globals::get('item_id');

        if (!empty($current) && !empty($item_id)) {
            $table = 'item_comments';
            $where = $sql->setWhere($table, null, 'item_id','=', $item_id);
            $where = $sql->setWhere($table, $where, 'status','=', 1);
            $order = $sql->setOrder($table, null, 'created_at', 'DESC');
            $total = $sql->getRow($table, $where);

            if ($total > $current) {
                $comments = $sql->getSelectResult($table, $where, $order, [$current, $per_page]);

                while ($comment = $sql->sql_fetch_assoc($comments)) {
                    $content .= sprintf('<li>
                                <div class="content-item bg-f6f6f6">
                                    <p>%s</p>
                                    <div class="icon-content">
                                        <span><i class="fa fa-user" aria-hidden="true"></i></span>
                                        <span>%s</span>
                                        <span>%sさん</span>
                                    </div>
                                </div>
                            </li>', $comment['comment'], DateTime::createFromFormat('Y-m-d H:i:s', $comment['created_at'])->format('2020年1月1日 H:i'), $comment['nickname']);
                }
            }

            $result['comment'] = $content;

            if (($total - $per_page) > $current) {
                $result['current'] = $current + $per_page;
            }
        }

        jsonEncode($result);
    }

    static  function loadMoreBattleRank()
    {
        jsonEncode(ccDraw::drawBattleRank([2 => Globals::get('type'), 'limit' => 40, 'offset' => 6, 3 => Globals::get('user-type')]));
    }

    static  function load_more_type_rank()
    {
        if (!empty(Globals::get('design_type'))) {
            jsonEncode(ccDraw::draw_market_type_rank([2 => Globals::get('type'), 'limit' => 40, 3 => Globals::get('market-type'), 'offset' => ccDraw::TYPE_RANK_MAX - 1, 4 => Globals::get('design_type'), 5 => Globals::get('user_campaign')]));
        } else {
            jsonEncode(ccDraw::draw_market_type_rank([2 => Globals::get('type'), 'limit' => 40, 3 => Globals::get('market-type'), 'offset' => ccDraw::TYPE_RANK_MAX - 1]));
        }
    }

    static  function loadMoreBattleMessages()
    {
        global $sql;

        $result = [
            'current' => 0,
            'message' => '',
        ];

        $content = '';
        $per_page = 4;
        $current = Globals::get('current');

        if (!empty($current)) {
            $table = 'battle_messages';
            $where = $sql->setWhere($table, null, 'status','=', 1);
            $order = $sql->setOrder($table, null, 'created_at', 'DESC');
            $total = $sql->getRow($table, $where);

            if ($total > $current) {
                $battle_messages = $sql->getSelectResult($table, $where, $order, [$current, $per_page]);

                while ($battle_message = $sql->sql_fetch_assoc($battle_messages)) {
                    $content .= getBattleMessage($battle_message);
                }
            }

            $result['message'] = $content;

            if (($total - $per_page) > $current) {
                $result['current'] = $current + $per_page;
            }
        }

        jsonEncode($result);
    }

    static function getTweets()
    {
        jsonEncode(ccDraw::drawMessages());
    }

    static function updateBlankItemStockNumber($master_item_type_data)
    {
        global $sql;

        $stock_index = 0;
        $blank_item_stocks      = [];
        $master_item_type_subs  = [];
        $master_item_type_sizes = [];
        $blank_item_stock_number_value = '';
        $insert = 'INSERT INTO `blank_item_stock_numbers` (item_type, stock, updated_at) VALUES %s ON DUPLICATE KEY UPDATE item_type=values(item_type), stock=values(stock), updated_at=values(updated_at);';
        $blank_item_stock_result = $sql->rawQuery('SELECT item_code, item_sub_code, item_size_code FROM blank_item_stock');
        $master_item_type_sub_result = $sql->rawQuery('SELECT item_type, item_code FROM master_item_type_sub');
        $master_item_type_size_result = $sql->rawQuery('SELECT item_type, item_code FROM master_item_type_size');

        foreach ($master_item_type_sub_result as $master_item_type_sub) {
            $master_item_type_subs[$master_item_type_sub['item_type']][] = $master_item_type_sub['item_code'];
        }

        foreach ($master_item_type_size_result as $master_item_type_size) {
            $master_item_type_sizes[$master_item_type_size['item_type']][] = $master_item_type_size['item_code'];
        }

        foreach ($blank_item_stock_result as $blank_item_stock) {
            if (in_array($blank_item_stock['item_code'], $master_item_type_data['item_codes'])) {
                $item_type = array_search($blank_item_stock['item_code'], $master_item_type_data['item_codes']);

                if (isset($master_item_type_data['states'][$item_type]) && $master_item_type_data['states'][$item_type] == 1) {
                    if (empty($blank_item_stocks[$item_type])) {
                        $blank_item_stocks[$item_type] = 0;
                    }

                    if (!empty($master_item_type_subs[$item_type]) && !empty($master_item_type_sizes[$item_type]) &&
                        in_array($blank_item_stock['item_sub_code'], $master_item_type_subs[$item_type]) &&
                        in_array($blank_item_stock['item_size_code'], $master_item_type_sizes[$item_type])) {
                        $blank_item_stocks[$item_type] += 1;
                    }
                }
            }
        }

        foreach ($blank_item_stocks as $item_type => $blank_item_stock) {
            insert_or_update($stock_index, [$item_type, $blank_item_stock, date('Y-m-d')], $insert, $blank_item_stock_number_value);
        }

        foreach ($master_item_type_data['states'] as $item_type => $state) {
            if ($state == 1 && !in_array($item_type, array_keys($blank_item_stocks))) {
                insert_or_update($stock_index, [$item_type, 0, date('Y-m-d')], $insert, $blank_item_stock_number_value);
            }
        }

        insert_or_update($stock_index, [], $insert, $blank_item_stock_number_value, true);
    }

    static function productSizes()
    {
        global $sql;

        $items          = [];
        $item_id        = Globals::get('item_id');
        $mit_size_table = 'master_item_type_size';
        $item           = $sql->selectRecord('item', $item_id);

        if (!empty($item)) {
            $mit_rec = $sql->selectRecord('master_item_type', $item['item_type']);
            $mit_sub_rec = $sql->selectRecord('master_item_type_sub', $item['item_type_sub']);
            $battle_item = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('%s AND item.id = "%s"', BATTLE_ITEM_QUERY, $item['id'])));

            if (!empty($battle_item)) {
                $where = $sql->setWhere($mit_size_table, null, 'id', '!=', 'ITSI7298');
            } else {
                $where = $sql->setWhere($mit_size_table, null, "state", "=", 1);
            }

            $tmp_array = array();
            $result = $sql->queryRaw('item_stock',"SELECT *,TRIM(LEADING '0' FROM item_type_sub_code) as item_type_sub_code_f from item_stock WHERE item = '".$mit_rec["item_code"]."' HAVING item_type_sub_code_f = '".ltrim($mit_sub_rec['item_code'],"0")."'");

            while($rec = $sql->sql_fetch_assoc($result))
            {
                $rec2 = $sql->sql_fetch_assoc($sql->queryRaw($mit_size_table,"SELECT *,TRIM(LEADING '0' FROM item_code) as item_code_f from master_item_type_size WHERE item_type = '" . $c[3] . "' HAVING item_code_f = '".ltrim($rec["item_type_size_code"],"0")."'"));

                $tmp_array[] = $rec2["id"];
            }

            $where = $sql->setWhere($mit_size_table, $where, "item_type", "=", $item['item_type']);
            $order = $sql->setOrder($mit_size_table, null, "wait", "ASC");
            $result = $sql->getSelectResult($mit_size_table, $where, $order);
            while($rec = $sql->sql_fetch_assoc($result))
            {
                if (!empty($battle_item) && in_array($rec['id'], ARTIST_SIZES)) {
                    $rec['state'] = 1;
                }

                if (!(in_array($rec['id'], $tmp_array) || $mit_rec['state'] == 0
                    || $mit_sub_rec['state'] == 0
                    || $rec['state'] == 0)) {
                    $items[] = [
                        'color_stock' => [],
                        'id' => (string)$rec['id'],
                        'is_main' => (string)$rec['is_main'],
                        'state' => (int)$rec['state'],
                        'title' => (string)$rec['name'],
                    ];
                }
            }
        }

        jsonEncode(['sizes' => $items]);
    }

    static function addOrderPackage() {

        global $sql;
        $list = Globals::get("list");
        $list = explode("/", $list);
        $count = 0;
        $list_factory = array();
        $list_pay_rec = array();
        foreach ($list as $id) {
            $pay_rec = $sql->selectRecord('pay',$id);
            if (!empty($pay_rec['factory_id'])) $list_factory[] = $pay_rec['factory_id'];
            $list_pay_rec[] = $pay_rec;
        }
        $table = 'order_packages';
        $date = date('ymd');
        $order_package_id = 'P'.$date.'1';
        $count_package = count($list_factory);
        $list_factory = array_unique($list_factory);
        foreach ($list_factory as $fac_id) {
            $order = $sql->setOrder($table,null,'id','DESC');
            $current_package = $sql->sql_fetch_assoc($sql->getSelectResult($table,null,$order));
            if($current_package) {
                if(substr($current_package['id'],1,6) == $date) {
                    $num = substr($current_package['id'],7,1);
                    $order_package_id = 'P'.$date.($num + 1);
                }
            }

            $rec = $sql->setData($table,null,'id',$order_package_id);
            $rec = $sql->setData($table,$rec,'factory_id',$fac_id);
            $rec = $sql->setData($table,$rec,'status','not_created');
            $rec = $sql->setData($table,$rec,'created_at',date("Y-m-d H:i:s"));
            $sql->addRecord($table,$rec);
            $count_pay = 0;
            foreach ($list_pay_rec as $pay) {
                if($pay['factory_id'] == $fac_id) {
                    $update = $sql->setData('pay',null,'order_package_id',$order_package_id);
                    $sql->updateRecord('pay',$update,$pay['id']);
                    $count_pay ++;
                }
            }

            $update = $sql->setData($table,null,'pay_count',$count_pay);
            $sql->updateRecord($table,$update,$order_package_id);
            $count += $count_pay;
        }

        if($count == $count_package) {
            print_r('追加に成功しました。');
        } else print_r('はエラーになったので、もう一度お試しください。');

    }

    static function changePackageStatus() {
        global $sql;
        $result = array();
        if($id = Globals::get('id')) {
            $table = 'order_packages';
            $package_rec = $sql->selectRecord($table,$id);
            $update = $sql->setData($table,null,'status','downloaded');
            $update = $sql->setData($table,$update,'updated_at',date("Y-m-d H:i:s"));
            $sql->updateRecord($table,$update,$id);
        }

        $result['content'] = 'ダウンロード済み';
        $result['url'] = $package_rec['file_url'];
        $result['updated_at'] = date("Y-m-d H:i:s");

        jsonEncode($result);
    }

    static function update_delivery_state() {
        if(Globals::files('file')) {
            global $sql;
            $import_file = Globals::files("file");
            $path = 'file/';
            if ($import_file) {
                move_uploaded_file($import_file['tmp_name'],$path.$import_file['name']);
                if (($handle = fopen($path.$import_file['name'], "r")) !== false) {
                    $count = 0;
                    while(($data = fgetcsv($handle, '8000', ",")) !== false ) {
                        if($count > 0) {
                            $pay_num = $data[0];
                            $where = $sql->setWhere('pay',null,'pay_num','=',$pay_num);
                            $pay = $sql->sql_fetch_assoc($sql->getSelectResult('pay',$where));
                            if(isset($pay)) {
                                $pay['tracking_number'] = isset($data[3]) ? $data[3] : "";
                                $pay['send_datetime'] = isset($data[4]) ? $data[4] : "";
                                $pay['delivery_service'] = 'yamato_spicelife'; // 現状はヤマトB2のCSVファイルにしか対応していないため暫定で固定値をセット
                                $sql->updateRecord('pay', $pay, $pay['id']);
                                changePayDelivery($pay['id'], 1);
                            }
                        }
                        $count ++;
                    }
                }
                fclose($handle);
            }

            if (!isset($_SERVER['HTTP_REFERER'])) {
                if ($ref = Globals::session("INNER_REFERE")) HttpUtil::location($ref);
            }
            HttpUtil::location($_SERVER['HTTP_REFERER']);
        }
    }

    static function getItemWeb()
    {
        $category = ccDraw::drawLiMasterItemWeb(array(2 => 'item'));
        $category_sp = ccDraw::drawLiMasterItemWeb(array(2 => 'item',3 => 'sp'));
        $data = [
                  'category' => $category,
                  'category_sp' => $category_sp,
        ];
        jsonEncode($data);
    }

    static function getInputAssetTemplate()
    {
        $tmp = drawInputAssetTemplate(Globals::get('template_id'));
        jsonEncode(['input' => $tmp]);
    }

    static function getPreviews()
    {
        global $sql;

        if ($sql->keySelectRecord('users_sessions', 'token', Globals::get('session'))) {
            $item = [
                'item_image1' => Globals::get('image'),
                'item_type' => Globals::get('item_type'),
                'item_type_sub' => Globals::get('color_id'),
            ];

            jsonEncode(generate_item_previews($item));
        }
    }

    static function previewAble()
    {
        $items = [];

        foreach (COLOR_PREVIEW as $item_id => $color) {
            $items[$item_id] = array_keys(COLOR_PREVIEW[$item_id]);
        }

        jsonEncode($items);
    }

    static function user_cancel_order_3rd(){
        global $sql;

        $data =0;
        $table = 'periodic_orders';
        $cancelled_ids = [];

        if(Globals::session("LOGIN_ID") && !empty(Globals::session("LOGIN_ID"))){
            $periodic_orders = $sql->rawQuery(sprintf('SELECT * FROM periodic_orders WHERE user_id = "%s"', Globals::session('LOGIN_ID')));

            if ($periodic_orders->num_rows >= 1) {
                foreach ($periodic_orders as $periodic_order) {
                    if (date_diff(date_create($periodic_order['last_ordered_at']), date_create())->format("%a") <= 10) {
                        $sql->deleteRecord($table, $periodic_order['id']);
                    } else {
                        if (empty($periodic_order['cancelled_at'])) {
                            $cancelled_ids[] = $periodic_order['id'];
                        }
                    }
                }

                if (!empty($cancelled_ids)) {
                    $sql->rawQuery(sprintf('UPDATE periodic_orders set cancelled_at = "%s", note = "%s" WHERE id IN ("%s")', date('Y-m-d'), $sql->escape(Globals::get('cancel_note')), implode('","', $cancelled_ids)));
                }

                $data =1;
                Globals::setSession('IS_PERIODIC_USER', false);
            }
        }

        jsonEncode($data);
    }

    static function downCsvShippingJapan($data)
    {
        ini_set('memory_limit', '200m');
        global $sql;

        if(!$list = Globals::get("list")) jsonEncode($data);
        $list = str_replace(',', '","', $list);

        $query = sprintf("SELECT
	                                pay.pay_num,
	                                pay.add_text,
                                    pay.`name`,
                                    pay.tel,
                                    sum( pay_item.item_row ) AS 'number' 
                                FROM
                                    pay
                                    INNER JOIN pay_item ON pay_item.pay = pay.id 
                                WHERE
                                    pay IN (%s)
                                GROUP BY
                                    pay.id 
                                HAVING
                                    `number` < 7", '"' . $list . '"');

        $result = $sql->rawQuery($query);

        $filename = "csv_shipping_japan".date("Y-m-d H:i:s").".csv";
        $contents = ['お客様側管理番号','お問い合わせ番号','代表お問い合わせ番号','発送予定日','発送予定時間区分','出荷期限日','到着期限日','郵便種別','保冷種別','元／着払／代引','書留／セキュリティ種別','配達時間帯指定郵便種別','送り状種別（※２）','お届け先 コード','お届け先 郵便番号','お届け先 住所1','お届け先 住所2','お届け先 住所3','お届け先 名称1','お届け先 名称2','お届け先 敬称区分','お届け先 電話番号','お届け先 メールアドレス１','お届け先 局留め区分','お届け先 局留め郵便局名','お届け先 局留めメール使用区分','お届け先 局留め郵便番号','お届け先 配達予告メール使用区分','お届け先 再配達予告メール使用区分','ご依頼主 コード','ご依頼主 集荷先と同一区分','ご依頼主 郵便番号','ご依頼主 住所1','ご依頼主 住所2','ご依頼主 住所3','ご依頼主 名称1','ご依頼主 名称2','ご依頼主 敬称','ご依頼主 電話番号','ご依頼主 メールアドレス１','ご依頼主 荷送人指図区分','ご依頼主 お届け通知メール使用区分','ご依頼主 お届け通知はがき使用区分','集荷先 コード','集荷先連携可否区分','集荷先 会社コード','集荷先 依頼先店所','集荷先 郵便番号','集荷先 住所1','集荷先 住所2','集荷先 住所3','集荷先 名称1','集荷先 名称2','集荷先 敬称','集荷先 電話番号','受注番号','こわれもの区分','なまもの区分','ビン類区分','逆さま厳禁区分','下積み厳禁区分','商品サイズ区分','重量（ｇ）','25kg超重量物区分','損害要償額','速達・配達日指定種別','配達指定日／希望日','配達時間帯区分','差出方法区分','ゆうパック複数個割引','ゆうパック同一割引','セット商品コード','セット品名ラベル印字区分','複数個口数','記事名１','記事名２','フリー項目０１','フリー項目０２','フリー項目０３','フリー項目０４','フリー項目０５','フリー項目０６','フリー項目０７','フリー項目０８','フリー項目０９','フリー項目１０','空港利用区分','空港・局／支店名','航空会社名','利用便名','レジャー区分','プレー・搭乗日','プレー・搭乗時間','クラブ本数','復路集貨日','出荷先登録名','集荷希望区分','集荷日付','集荷時間帯区分','支店連携先選択用名称','代引金額','代引消費税金額','送り状発行年月日','商品番号（明細）','品名（明細）','個数（明細）','重量（ｇ）　（明細）','単価（明細）','金額（明細）','商品備考０１（明細）','商品備考０２（明細）','商品備考０３（明細）','商品備考０４（明細）','商品備考０５（明細）','商品備考０６（明細）','商品備考０７（明細）','商品備考０８（明細）','商品備考０９（明細）','商品備考１０（明細）','お客様指定配送種類'];
        $contents = '"' . implode('","', $contents) . '"';


        while($rec = $sql->sql_fetch_assoc($result))
        {
            $contents_item = ['','','','',5,'','',0,0,0,'','','','〒',$rec['add_text'],$rec['add_text'],$rec['pay_num'],$rec['name'],'ゆうパケ',0,$rec['tel'],'',0,'','','','','','','','久乃木井部15','石川県鹿島郡中能登町','','','丸井織物株式会社','',0,'0120-84-4321','','','','','','','','','','','','','','','','',$rec['pay_num'],'','','','','',20,'','','','','','','','','','','',1,'','','衣料品','','',$rec['pay_num'],'','','','','','','','','','','','','','','','','','','','','','','','','衣料品','','','','','','','','','','','','','','',4];
            $contents .= "\n" . '"' . implode('","', $contents_item) . '"';
        }

        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        echo $contents;

        ob_end_flush();
        exit;
    }

    static function downloadCsv3rdMask($data)
    {
        ini_set('memory_limit', '200m');
        global $sql;

        if(!$list = Globals::get("list")) jsonEncode($data);
        $list = str_replace(',', '","', $list);

        $select = "SELECT
                            pay.pay_num,
                            pay.user,
                            master_item_type.name,
                            sum( item_row ) AS count FROM pay_item ";
        $inner_join = 'INNER JOIN pay ON pay.id = pay_item.pay
                           INNER JOIN user ON pay.user = user.id
                           INNER JOIN master_item_type ON pay_item.item_type = master_item_type.id';
        $where = sprintf(' WHERE
                            pay_item.pay IN (%s)', '"' . $list . '"');
        $where .= sprintf(' AND pay_item.item IN ("%s","%s")', constants::MASK['item_id'], constants::PERIODIC_MASK['item_id']);

        $query = $select . $inner_join .$where;

        $result = $sql->rawQuery($query);
        $filename = "mask注文履歴_".time().".csv";
        $contents = '"Order","User","Item","Stock"'."\n";
        while ($tmp_rec = $sql->sql_fetch_assoc($result)) {
            $contents .= '"' . $tmp_rec['pay_num'] . '","' . $tmp_rec['user'] . '","' . $tmp_rec['name'] .
                '","' . $tmp_rec['count'] . '"';
            $contents .= "\n";
        }

        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        echo $contents;

        ob_end_flush();
        exit;
    }

    static function getItemStockData() {
        if(!$item_id = Globals::get('item_type')) return;
        $data = getListBlankItem($item_id);
        jsonEncode($data);exit;
    }

    static function getListColorMasterItem() {
        global $sql;
        $data = array();
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table,null,'item_type','=',Globals::get('item_type_id'));
        $where = $sql->setWhere($mit_sub_table,$where,'state','=',1);

        $list_mit_sub = $sql->getSelectResult($mit_sub_table,$where);

        if($list_mit_sub->num_rows) {
            while ($mit_sub_rec = $sql->sql_fetch_assoc($list_mit_sub)) {
                $data[] = array('item_type_sub' => $mit_sub_rec['id'], 'name' => $mit_sub_rec['name']);
            }
        }

        jsonEncode($data);
    }

    static function getListSideMasterItem() {
        global $sql;
        $data = array();
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table,null,'item_type','=',Globals::get('item_type_id'));
        $where = $sql->setWhere($mit_sub_table,$where,'is_main','=',1);
        $where = $sql->setWhere($mit_sub_table,$where,'state','=',1);

        $mit_sub_rec = $sql->sql_fetch_assoc($sql->getSelectResult($mit_sub_table,$where));

        if($mit_sub_rec) {
            $mit_sub_side_table = 'master_item_type_sub_sides';
            $where = $sql->setWhere($mit_sub_side_table,null,'color_id','=',$mit_sub_rec['id']);
            $where = $sql->setWhere($mit_sub_side_table,$where,'state','=',1);
            $list_side = $sql->getSelectResult($mit_sub_side_table,$where);

            while ($mit_sub_side_rec = $sql->sql_fetch_assoc($list_side)) {
                $data[] = array('side_name' => $mit_sub_side_rec['side_name'], 'title' => $mit_sub_side_rec['title']);
            }
        }

        jsonEncode($data);
    }

    static function getScaleSizeItemType() {
        global $sql;
        $data = array();
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table,null,'item_type','=',Globals::get('item_type_id'));
        $where = $sql->setWhere($mit_sub_table,$where,'is_main','=',1);
        $where = $sql->setWhere($mit_sub_table,$where,'state','=',1);
        $result_mit_sub = $sql->getSelectResult($mit_sub_table,$where);
        if($result_mit_sub->num_rows) {
            $mit_sub_rec = $sql->sql_fetch_assoc($result_mit_sub);
        }

        if($mit_sub_rec) {
            $mit_sub_side_table = 'master_item_type_sub_sides';
            $where = $sql->setWhere($mit_sub_side_table,null,'color_id','=',$mit_sub_rec['id']);
            $where = $sql->setWhere($mit_sub_side_table,$where,'state','=',1);
            $where = $sql->setWhere($mit_sub_side_table,$where,'side_name','=',Globals::get('side_name'));

            $result_mit_sub_side = $sql->getSelectResult($mit_sub_side_table,$where);
            if($result_mit_sub_side->num_rows) {
                $mit_sub_side_rec = $sql->sql_fetch_assoc($result_mit_sub_side);
            }

            if($mit_sub_side_rec) {
                $content = json_decode($mit_sub_side_rec['content']);
                $content = obj2arr($content);
                $current_border = $content['border']['cm'];
                $current_size = $content['size']['cm'];
                $max_size = max($current_size);
                if($max_size > 1000) {
                    $scale_per = 1000/$max_size;
                    $new_width = floor($current_border['width']*$scale_per);
                    $new_height = floor($current_border['height']*$scale_per);
                    $data['width'] = $new_width;
                    $data['height'] = $new_height;
                } else {
                    $data['width'] = $current_border['width'];
                    $data['height'] = $current_border['height'];
                }
            } else {
                $data['error'] = 1;
            }

        } else {
            $data['error'] = 1;
        }

        jsonEncode($data);
    }

    static function getS3UrlFromImage() {
        ini_set('memory_limit','200M');
        $url = '';
        if($file = Globals::files('file'))  {
            $session = Globals::session("DRAW_TOOL_SESSION");
            if(isset($_POST['series_new'])){
                if($_POST['series_new']) Globals::setSession("ADD_ITEM_INFO",NULL);
            }

            $file_info = array();
            $file_info['mime_type'] = $file['type'];
            $file_info['extension'] = pathinfo($file['name'])['extension'];
            $file_info['content'] = 'data:image/' . $file_info['extension'] . ';base64,' . base64_encode(file_get_contents($file['tmp_name']));
            $post_data = array();
            $post_data['session']= $session;
            $post_data['File']= $file_info;

            $url = ApiConfig::HOST."/designs/design/file/upload";
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            $image_info = curl_exec($ch);
            curl_close($ch);

            $image_info = json_decode($image_info);
            $image_info = obj2arr($image_info);

            $url = $image_info['file_url'];
        }

        print_r($url);

    }

    static function getS3UrlFromFile() {
        $url = '';
        if($file = Globals::files('file'))  {
            $url = SystemUtil::doFileUpload($file);
        }

        print_r($url);
    }


    static function getListItemRecommend() {
        ini_set('memory_limit', '100m');
        $content = '';
        $image_url = Globals::get('image_url');
        $session = Globals::session("DRAW_TOOL_SESSION");
        $add_item_info = Globals::session("ADD_ITEM_INFO");
        if(($image_url==null)||($image_url=="undefined")){
            $image_url= $add_item_info["image_url"];
        }
        else
        {
            $add_item_info["image_url"]=$image_url;
        }

        $url = ApiConfig::HOST."/design/item-publish";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $list_item_recommend = curl_exec($ch);
        $list_item_recommend = json_decode($list_item_recommend,true);
        $list_item_type = $list_item_recommend['list_item_type_recommend'];




        if(!empty($add_item_info['list_item'])) {
            $list_item_type = $add_item_info['list_item'];
        } else {
            foreach ($list_item_type as $key =>  $value) {
                $value['image_preview_type'] = 'svg';
                $value['in_top'] = '1';
                $list_item_type[$value['item_type_id']] = $value;
                unset($list_item_type[$key]);
            }
        }

        $list_color = array();
        $list_side = array();
        $special_draw = array();
        $color_draw = array();

        foreach ($list_item_type as $item_type) {
            if($item_type['image_preview_type'] == 'svg' && $item_type['in_top']) {
                $list_color[] = $item_type['color_id'];
                $list_side[] = $item_type['side_name'];
                $special_draw[] = $item_type['special_draw'];
                $color_draw[] = $item_type['color_draw'];
            }
        }

        $url = ApiConfig::HOST.'/designs/design/svg-preview';

        $post_data = array();
        $post_data['session']= $session;
        $post_data['color_ids']= $list_color;
        $post_data['sides'] = $list_side;
        $post_data['special_draw']= $special_draw;
        $post_data['color_draw'] = $color_draw;
        $post_data['image_url'] = $image_url;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
        $list_svg = curl_exec($ch);
        curl_close($ch);

        $list_svg = json_decode($list_svg,true);
        foreach ($list_item_type as $key => $value) {
            if(!empty($list_svg[$value['color_id']])) {
                $value['image_preview_content'] = $list_svg[$value['color_id']]['svg'];
            } 
            if(empty($value['special_draw'])){
                $value['special_draw']=0;
            } else if($value['special_draw']==''){
                $value['special_draw']=0;
            }
            $list_item_type[$key] = $value;
        }

        $add_item_info['list_item'] = $list_item_type;
        Globals::setSession('ADD_ITEM_INFO',$add_item_info);

        foreach ($list_item_type as $value) {
            $stringNew=preg_replace("/\s+/", "", $value['image_preview_content']);
            if(!filter_var($stringNew, FILTER_VALIDATE_URL)) 
            {  
                $image_content = $value['image_preview_content'];
            } else {
                $value['image_preview_content']= $stringNew;
                $image_content = "<img src='{$value['image_preview_content']}' alt='{$value['image_preview_content']}'>";
            }
            $data_color = $value["data_color"];
            $content_data_color ='';
            foreach ($data_color as $key=> $colorx){
                if($key==$value["color_id"]){
                    $content_data_color .="<option value='{$key}' selected >{$colorx}</option>";
                }
                else
                {
                    $content_data_color .="<option value='{$key}'>{$colorx}</option>";
                }
            }
            $display = !empty($value['group_id']) ? 'display-none' : '';
            $data_group = !empty($value['group_id']) ? "data-group='{$value['group_id']}'" : '';
            $colordraw = $value['color_draw'];
            if(strlen($colordraw)<4) $colordraw ='rgba(0,0,0,1)';
            if(strlen($value['image_preview_content'])>10){
                $content.= "<div class='product-card recommend-item $display' $data_group>
                            <a class='btn-edit-design' href='javascript:void(0)'  special-draw-name='{$value['special_draw']}' color-draw-name='{$colordraw}' data-color-id='{$value['color_id']}' data-side-name='{$value['side_name']}' data-item-type='{$value['item_type_id']}'><i class='fa fa-edit'></i></a>
                            <label for='color_{$value['color_id']}'>
                                {$image_content}
                                <div class='title-header'>
                                <div class='custom-control custom-checkbox' style='padding-left: 0px'>
                                    <div class='row  d-flex'>
                                        <div class='col-2'>
                                            <input class='custom-control-input' type='checkbox' name='color_ids[]' value='{$value['color_id']}' id='color_{$value['color_id']}' $data_group checked>
                                            <input type='checkbox' name='side_ids[]' value='{$value['side_name']}' id='side_{$value['color_id']}' hidden $data_group checked>
                                            <input type='checkbox' name='special_draw[]' value='{$value['special_draw']}' id='side_{$value['color_id']}' hidden  $data_group checked>
                                            <input type='checkbox' name='color_draw[]' value='{$value['color_draw']}' id='side_{$value['color_id']}' hidden  $data_group checked>
                                            <label class='custom-control-label' for='color_{$value['color_id']}'></label>
                                        </div>
                                        <div class='col-10' >
                                            <span class='title'>{$value['item_type_name']}</span>
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <select name='item_type_sub' class='jcf-reset-appearance' style='padding: 6px 10px; width: 100%; margin-top: 15px;' data-side-title='font' data-product-name='{$value['item_type_name']}' special-draw-name='{$value['special_draw']}' color-draw-name='{$colordraw}' data-color-id='{$value['color_id']}' data-side-name='{$value['side_name']}' data-item-type='{$value['item_type_id']}' data-product-id='{$value['item_type_id']}'  >
                                            {$content_data_color}
                                        </select>
                                    </div>
                                </div>
                                </div>
                            </label>
                            </div>";
            }
        }

        $content .= "<div class='product-card add-item'>
                          <a class='btn-show-category' href='javascript:void(0)'><i class='fa fa-plus'></i> 商品を追加する</a>
                     </div>";
        print_r($content); exit;
    }

    static function getListItemTypeAddItem() {
        $content = '';
        if($category_id = Globals::get('category_id')) {
            global $sql;
            $query_ignore_item = '';
            $add_item_info = Globals::session("ADD_ITEM_INFO");
            if(!empty($add_item_info['custom_item'])) {
                $query_ignore_item .= 'AND id NOT IN (';
                $list_custom_product = Globals::session('ADD_ITEM_INFO')['custom_item'];
                foreach ($list_custom_product as $product_info) {
                    $query_ignore_item .= "'{$product_info['item_type_id']}',";
                }

                $query_ignore_item = rtrim($query_ignore_item,',');
                $query_ignore_item .= ')';
            }

            $query_item_type = "SELECT * FROM master_item_type WHERE state = 1 AND category_id = '$category_id'  $query_ignore_item ORDER BY master_item_type.`order`";
            $list_item_type = $sql->rawQuery($query_item_type);
            if($list_item_type->num_rows) {
                $content .= "<div class='content'>";
                while ($item_type_rec = $sql->sql_fetch_assoc($list_item_type)) {
                    $preview_image = '';
                    $query_default_color = "SELECT id,name FROM master_item_type_sub WHERE item_type = '{$item_type_rec['id']}' AND is_main = 1";
                    $default_color_rec = $sql->sql_fetch_assoc($sql->rawQuery($query_default_color));
                    $enabbleAdd=true;
                    if($default_color_rec) {
                        $query_default_side = "SELECT preview_url, side_name,title FROM master_item_type_sub_sides WHERE color_id = '{$default_color_rec['id']}' AND is_main = 1";
                        $default_side_rec = $sql->sql_fetch_assoc($sql->rawQuery($query_default_side));
                        if($default_side_rec) {
                            $preview_image = $default_side_rec['preview_url'];
                        }
                    }
                    else
                    {
                        $enabbleAdd =false;
                    }
                    //$item_type_rec['special_draw'] $item_type_rec['color_draw']   special_draw=$(this).attr('special-draw-name'); color_draw=$(this).attr('color-draw-name');
                    $colordraw = $item_type_rec['color_draw'];
                    $special_draw = $item_type_rec['special_draw'];
                    if(strlen($colordraw)<4) $colordraw ='rgba(0,0,0,1)';
                    if(!is_numeric($special_draw)) $special_draw =0;

                    foreach ($add_item_info['list_item'] as $key=> $colorView){
                        if(($key==$item_type_rec['id'])&&($colorView["color_id"]==$default_color_rec['id'])){
                            $enabbleAdd =false;
                        }
                    }
                    if($enabbleAdd){
                        $content .= "<div class='product-card product' special-draw-name='{$special_draw}' data-item-type='{$item_type_rec['id']}'  
                                    color-draw-name='{$colordraw}'  data-product-id='{$item_type_rec['id']}' data-product-name='{$item_type_rec['name']}' 
                                    data-color-id='{$default_color_rec['id']}' data-color-name='{$default_color_rec['name']}' 
                                    data-side-name='{$default_side_rec['side_name']}' data-side-title='{$default_side_rec['title']}'>
                                    <div class='title-header'>
                                        <div class='title'>{$item_type_rec['name']}</div>
                                        <div class='product-maker'>{$item_type_rec['maker']}</div>
                                        <div class='product-id'>{$item_type_rec['item_code_nominal']}</div>
                                    </div>
                                    <img class='preview' src='{$preview_image}' alt='{$item_type_rec['name']}'>
                                    <button class='action-button'>選択</button>
                                </div>";
                    }
                }

                $content .= "</div>";
            } else {
                $content .= "<h2>このカテゴリは商品がありません。</h2>".$query_item_type;
            }
        }
        print_r($content);exit;
       
    }

    static function AddItemTypeInput() {
        $content = '';
        $color = Globals::get('color_id');
        $color_name = Globals::get('color_name');
        $side = Globals::get('side');
        $side_title = Globals::get('side_title');
        $image_url = Globals::get('image_url');
        $product_id = Globals::get('product_id');
        $product_name = Globals::get('product_name');
        $special_draw = Globals::get('special_draw');
        $color_draw = Globals::get('color_draw');
        $data_color = array();
        $add_item_info = Globals::session("ADD_ITEM_INFO");
        if(($image_url==null)||($image_url=="undefined")){
            $image_url= $add_item_info["image_url"];
        }
        else
        {
            $add_item_info["image_url"]=$image_url;
        }

        if($color && $color_name && $side && $side_title && $image_url
            && $product_name && $product_id) {
            $url = ApiConfig::HOST.'/designs/design/svg-preview';
            $session = Globals::session("DRAW_TOOL_SESSION");
            $post_data = array();
            $post_data['session']= $session;
            $post_data['color_ids']= array($color);
            $post_data['sides'] = array($side);
            $post_data['special_draw']= array($special_draw);
            $post_data['color_draw'] = array($color_draw);
            $post_data['image_url'] = $image_url;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            $svg = curl_exec($ch);
            curl_close($ch);

            $svg = json_decode($svg,true);
            $image_content='';
            $content_data_color ='';
           
            foreach ($svg as $value) {
                if(!filter_var($value['svg'], FILTER_VALIDATE_URL))  {
                    $image_content = $value['svg'];
                    $value['image_preview_type']="svg";
                    
                } else {
                    $image_content = "<img src='{$value['svgData']}' alt='{$value['svgData']}'>";
                    $value['image_preview_type']="png";
                }
                $data_color = $value["data_color"];
                foreach ($data_color as $key=> $colorx){
                    if($key==$color){
                        $content_data_color .="<option value='{$key}' selected >{$colorx}</option>";
                    }
                    else
                    {
                        $content_data_color .="<option value='{$key}'>{$colorx}</option>";
                    }
                }
            }

           if(strlen($color_draw)<4) $color_draw ='rgba(0,0,0,1)';
           if(!is_numeric($special_draw)) $special_draw =0;

            $content.= "<div class='product-card recommend-item'>
                          <a class='btn-edit-design' href='javascript:void(0)' data-item-type='{$product_id}'  special-draw-name='{$special_draw}' color-draw-name='{$color_draw}' data-color-id='{$color}' data-side-name='{$side}'><i class='fa fa-edit'></i></a>
                          <label for='color_{$color}'>
                              {$image_content}
                              <div class='title-header'>
                              <div class='custom-control custom-checkbox ' style='padding-left: 0px'>
                                <div class='row d-flex'>
                                  <div class='col-2'>
                                    <input class='custom-control-input' type='checkbox' name='color_ids[]' value='{$color}' id='color_{$color}' checked>
                                    <input type='checkbox' name='side_ids[]' value='{$side}' id='side_{$color}' hidden checked>
                                    <input type='checkbox' name='special_draw[]' value='{$special_draw}' id='side_{$color}' hidden checked>
                                    <input type='checkbox' name='color_draw[]' value='{$color_draw}' id='side_{$color}' hidden checked>
                                    <label class='custom-control-label' for='color_{$color}'></label>
                                  </div>
                                  <div class='col-10'>
                                    <span class='title'>{$product_name}</span>
                                  </div>
                                </div>
                                <div class='row'>
                                    <select name='item_type_sub' class='jcf-reset-appearance' 
                                    style='padding: 6px 10px; width: 100%; margin-top: 15px' data-side-title='font' data-product-name='{$product_name}'
                                    special-draw-name='{$special_draw}' color-draw-name='{$color_draw}' 
                                    data-color-id='{$color}' data-side-name='{$side}' 
                                    data-item-type='{$product_id}' data-product-id='{$product_id}'  >
                                        {$content_data_color}
                                    </select>
                                </div>
                              </div>
                            </div>
                          </label>
                        </div>";
        }


       
        $new_item = array();
        $new_item['item_type_id'] = $product_id;
        $new_item['item_type_name'] = $product_name;
        $new_item['side_name'] = $side;
        $new_item['side_title'] = $side_title;
        $new_item['color_id'] = $color;
        $new_item['color_name'] = $color_name;
        $new_item['color_draw'] = $color_draw;
        $new_item['special_draw'] = $special_draw;
        $new_item['data_color'] = $data_color;
        $new_item['image_preview_type'] =  $svg[$color]['image_preview_type'];
        $new_item['image_preview_content'] = $svg[$color]['svg'];
        $add_item_info['list_item'][$product_id] = $new_item;
        Globals::setSession('ADD_ITEM_INFO',$add_item_info);

        print_r($content);exit;
    }

    
    static function EditItemTypeInput() {
        $content = '';
        $color = Globals::get('color_id');
        $color_change = Globals::get('color_change');
        $color_name = Globals::get('color_name');
        $side = Globals::get('side');
        $side_title = Globals::get('side_title');
        $image_url = Globals::get('image_url');
        $product_id = Globals::get('product_id');
        $product_name = Globals::get('product_name');
        $special_draw = Globals::get('special_draw');
        $color_draw = Globals::get('color_draw');
        $add_item_info = Globals::session("ADD_ITEM_INFO");

        if(($image_url==null)||($image_url=="undefined")){
            $image_url= $add_item_info["image_url"];
        }
        else
        {
            $add_item_info["image_url"]=$image_url;
        }

        if($color_change && $color_name && $side && $side_title && $image_url
            && $product_name && $product_id) {
            $url = ApiConfig::HOST.'/designs/design/svg-preview';
            $session = Globals::session("DRAW_TOOL_SESSION");
            $post_data = array();
            $post_data['session']= $session;
            $post_data['color_ids']= array($color_change);
            $post_data['sides'] = array($side);
            $post_data['special_draw']= array($special_draw);
            $post_data['color_draw'] = array($color_draw);
            $post_data['image_url'] = $image_url;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            $svg = curl_exec($ch);
            curl_close($ch);

            $svg = json_decode($svg,true);
            $image_content='';
            $content_data_color ='';
            foreach ($svg as $value) {
                if(!filter_var($value['svg'], FILTER_VALIDATE_URL))  {
                    $value['image_preview_type']="svg";
                    $image_content = $value['svg'];
                } else {
                    $value['image_preview_type']="png";
                    $image_content = "<img src='{$value['svgData']}' alt='{$value['svgData']}'>";
                }
                $data_color = $value["data_color"];
                foreach ($data_color as $key=> $colorx){
                    if($key==$color_change){
                        $content_data_color .="<option value='{$key}' selected >{$colorx}</option>";
                    }
                    else
                    {
                        $content_data_color .="<option value='{$key}'>{$colorx}</option>";
                    }
                }
            }

           if(strlen($color_draw)<4) $color_draw ='rgba(0,0,0,1)';
           if(!is_numeric($special_draw)) $special_draw =0;
            $content.= "
                          <a class='btn-edit-design' href='javascript:void(0)' special-draw-name='{$special_draw}' color-draw-name='{$color_draw}' data-color-id='{$color_change}' data-side-name='{$side}'><i class='fa fa-edit'></i></a>
                          <label for='color_{$color_change}'>
                              {$image_content}
                              <div class='title-header'>
                              <div class='custom-control custom-checkbox ' style='padding-left: 0px'>
                                <div class='row  d-flex'>
                                  <div class='col-2'>
                                    <input class='custom-control-input' type='checkbox' name='color_ids[]' value='{$color_change}' id='color_{$color_change}' checked>
                                    <input type='checkbox' name='side_ids[]' value='{$side}' id='side_{$color_change}' hidden checked>
                                    <input type='checkbox' name='special_draw[]' value='{$special_draw}' id='side_{$color_change}' hidden checked>
                                    <input type='checkbox' name='color_draw[]' value='{$color_draw}' id='side_{$color_change}' hidden checked>
                                    <label class='custom-control-label' for='color_{$color_change}'></label>
                                  </div>
                                  <div class='col-10'>
                                    <span class='title'>{$product_name}</span>
                                  </div>
                                </div>
                                <div class='row'>
                                    <select name='item_type_sub' class='jcf-reset-appearance' 
                                    style='padding: 6px 10px; width: 100%; margin-top: 15px' data-side-title='font' data-product-name='{$product_name}' 
                                    special-draw-name='{$special_draw}' color-draw-name='{$color_draw}' 
                                    data-color-id='{$color_change}' data-side-name='{$side}' 
                                    data-item-type='{$color_change}' data-product-id='{$color_change}'  >
                                        {$content_data_color}
                                    </select>
                                </div>
                              </div>
                            </div>
                          </label>
                        ";
        }

        foreach ($add_item_info['list_item'] as $key=> $colorView){
            if(($key==$product_id)&&($colorView["color_id"]==$color)){
                $add_item_info['list_item'][$product_id]['item_type_name'] = $product_name;
                $add_item_info['list_item'][$product_id]['color_id'] = $color_change;
                $add_item_info['list_item'][$product_id]['color_name'] = $color_name;
                $new_item['image_preview_type'] =  $svg[$color_change]['image_preview_type'];
                $add_item_info['list_item'][$product_id]['image_preview_content'] = $svg[$color_change]['svg'];
            }
        }
        Globals::setSession('ADD_ITEM_INFO',$add_item_info);

        print_r($content);exit;
    }

    static function getDesignByImage() {

        $color_id = Globals::get('color_id');
        $side = Globals::get('side');
        $image_url = Globals::get('image_url');
        $item_type = Globals::get('item_type');
        $add_item_info = Globals::session("ADD_ITEM_INFO");
        $color_draw = Globals::get('color_draw');
        $special_draw = Globals::get('special_draw');
        if(!is_numeric($special_draw)) $special_draw =0;
        if(($image_url==null)||($image_url=="undefined")){
            $image_url= $add_item_info["image_url"];
        }
        else
        {
            $add_item_info["image_url"]=$image_url;
        }

        if(!empty($add_item_info['list_item'][$item_type]['design_id'])) {
            $design_id = $add_item_info['list_item'][$item_type]['design_id'];
        } else {
            $url = ApiConfig::HOST.'/designs/design/mask-data';
            $post_data = array();
            $post_data['session']= Globals::session("DRAW_TOOL_SESSION");
            $post_data['color_ids'] = array($color_id);
            $post_data['color_draw'] = array($color_draw);
            $post_data['special_draw'] = array($special_draw);
            $post_data['design_images'] = array($side => $image_url);


            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
            $result = curl_exec($ch);
            curl_close($ch);

            $designs = json_decode($result,true);
            $design_id = $designs[$color_id]['design_id'];
        }


        $add_item_info = Globals::session("ADD_ITEM_INFO");
        $add_item_info['custom_design_item'] = array('color_id' => $color_id,'side' => $side,'design_id' => $design_id,'image_url'=>$image_url);
        Globals::setsession('ADD_ITEM_INFO',$add_item_info);

        print($result);exit;
    }

    static function goToDesignCustomTool() {
        $add_item_info = Globals::session('ADD_ITEM_INFO');
        $add_item_info['list_input_value'] = $_POST;

        if(!empty($add_item_info['custom_design_item'])) {
            $design_id = $add_item_info['custom_design_item']['design_id'];
            Globals::setSession('ADD_ITEM_INFO',$add_item_info);
            global $sql;
            $table='series_design_publish';
            $check_id  =0; 
            $is_update=false;
            $queryRaw = $sql->queryRaw($table, "SELECT * FROM series_design_publish WHERE user_id = '".Globals::session("LOGIN_ID")."' AND token='".Globals::session("DRAW_TOOL_SESSION")."' LIMIT 0, 1");
            while($result = $sql->sql_fetch_assoc($queryRaw))
            {
                $check_id=$result['id'];
                $is_update=true;
            }

            $post_data = array();
            $post_data['session']= Globals::session("DRAW_TOOL_SESSION");
            $post_data['design']= $add_item_info;
            $data_string = json_encode($post_data);
            $ch = curl_init(ApiConfig::HOST . '/designs/design/save-searial');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );
            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            $info_data= json_decode($result,true);
            $info_url='';
            if($httpcode == 200) {
                $info_url = $info_data["message"];
            } else {
                 SystemUtil::errorPage();
            }

            if($is_update==false){
                $check_id= SystemUtil::getUniqId($table, false, true);
                $tmp_rec = $sql->setData($table, $tmp_rec, "id", $check_id); 
                $tmp_rec = $sql->setData($table, $tmp_rec, "user_id", Globals::session("LOGIN_ID"));
                $tmp_rec = $sql->setData($table, $tmp_rec, "token_code", json_encode(Globals::session("TOKEN_CODE")));
                $tmp_rec = $sql->setData($table, $tmp_rec, "token", Globals::session("DRAW_TOOL_SESSION"));
                $tmp_rec = $sql->setData($table, $tmp_rec, "image", $add_item_info['custom_design_item']['image_url']);
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_path1", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_pre1", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_path2", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_pre2", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_path3", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_pre3", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_path4","");
                $tmp_rec = $sql->setData($table, $tmp_rec, "image_pre4", "");
                $tmp_rec = $sql->setData($table, $tmp_rec, "add_item_info", $info_url);
                $tmp_rec = $sql->setData($table, $tmp_rec, "created_at", date('Y-m-d H:i:s'));
                $tmp_rec = $sql->setData($table, $tmp_rec, "updated_at", date('Y-m-d H:i:s'));
                $tmp_rec = $sql->setData($table, $tmp_rec, "status", 0);
                $sql->addRecord($table, $tmp_rec);
            }
            else
            {
                $sql->updateRecord($table, array("token_code"=> json_encode(Globals::session("TOKEN_CODE")), 'status' => 0,"add_item_info"=>$info_url ), $check_id);
            }
        	//$host = DrawToolConfig::HOST.'?session=' .Globals::session("DRAW_TOOL_SESSION").'&design_id='.$design_id.'&test_dev=true&add_item=true&crf_certificate='.$check_id;
            $host = DrawToolConfig::HOST.'?session=' .Globals::session("DRAW_TOOL_SESSION").'&design_id='.$design_id.'&add_item=true&crf_certificate='.$check_id;
            header("Location: $host");
            exit;
        } else {
            SystemUtil::errorPage();
        }
    }

    static function callbackAddItem() {
        global $sql;
        $is_exist=false;
        $add_item_info = array();
        $table='series_design_publish';
        $queryRaw = $sql->queryRaw($table, "SELECT * FROM series_design_publish WHERE id = '".Globals::session("crf_certificate")."' AND status=0 AND token='".Globals::session("DRAW_TOOL_SESSION")."' LIMIT 0, 1");
        while($result = $sql->sql_fetch_assoc($queryRaw))
        {
            $is_exist=true;
            $url = $result['add_item_info'];
            $tockenCode= json_decode($result['token_code'],true);
            Globals::setSession("LOGIN_ID", $result['user_id']); 
            Globals::setSession("TOKEN_CODE",$tockenCode); 
            Globals::setSession("DRAW_TOOL_SESSION", $result['token']); 
            Globals::setPost("TOKEN_CODE", $tockenCode["regist"]["series"]);
            $headers = array(
                'Content-Type: application/json'
            );
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPGET , true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $resultFile = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);
            if($httpcode == 200) {
                $add_item_info = json_decode($resultFile, true);
            } else {
                $add_item_info =array();
                 SystemUtil::errorPage();
            }

        }

        if($is_exist==false)  SystemUtil::errorPage();
        $add_item_info1 =Globals::post('session');
        if(Globals::post('session') != Globals::session('DRAW_TOOL_SESSION')) SystemUtil::errorPage();

        $item_type = Globals::post('item_type');
        $color_id = Globals::post('item_type_sub');
        if($image_pre1 = Globals::post('image_pre1')) {
            $image_preview = $image_pre1;
            $side = 1;
            $result["image_path1"]=Globals::post('image_path1');
            $result["image_pre1"]=Globals::post('image_pre1');
        } else if ($image_pre2 = Globals::post('image_pre2')) {
            $image_preview = $image_pre2;
            $side = 2;
            $result["image_path2"]=Globals::post('image_path2');
            $result["image_pre2"]=Globals::post('image_pre2');
        } else if ($image_pre3 = Globals::post('image_pre3')) {
            $image_preview = $image_pre3;
            $side = 3;
            $result["image_path3"]=Globals::post('image_path3');
            $result["image_pre3"]=Globals::post('image_pre3');
        } else if ($image_pre4 = Globals::post('image_pre4')) {
            $image_preview = $image_pre4;
            $side = 4;
            $result["image_path4"]=Globals::post('image_path4');
            $result["image_pre4"]=Globals::post('image_pre4');
        }
        $result['status']=1;
        $sql->updateRecord($table,$result, Globals::session("crf_certificate"));
        if(empty($add_item_info['list_item'][$item_type])) SystemUtil::errorPage();
        $current_color_id = $add_item_info['list_item'][$item_type]['color_id'];
        $add_item_info['list_item'][$item_type]['image_preview_type'] = 'img';
        $add_item_info['list_item'][$item_type]['image_preview_content'] = $image_preview;
        $add_item_info['list_item'][$item_type]['color_id'] = $color_id;
        $add_item_info['list_item'][$item_type]['side_name'] = $side;
        $add_item_info['list_item'][$item_type]['design_id'] = Globals::post('image_id');

        $list_input_value = $add_item_info['list_input_value'];
        $list_input_value['step'] = 1;
        foreach ($list_input_value['color_ids'] as $key => $value) {
            if($value == $current_color_id) {
                $list_input_value['color_ids'][$key] = $color_id;
                $list_input_value['side_ids'][$key] = $side;
            }
        }


        Globals::setSession('ADD_ITEM_INFO',$add_item_info);

        ob_end_clean();
        ob_start();

        $hidden = "";
        foreach($list_input_value as $key => $val){
            if($key != 'color_ids' && $key != 'side_ids') {
                $hidden .= '<input type="hidden" name="'.h($key).'" value="'.h($val).'" />'."\n";
            } else {
                foreach ($list_input_value[$key] as $input) {
                    $hidden .= '<input type="hidden" name="'.h($key).'[]" value="'.h($input).'" />'."\n";
                }
            }

        }

        $tmp = '
			<html>
			<head>
			<title></title>
			</head>
			<body onload="document.form.submit();">
			<form action="'.'/designs/series_select'.'" name="form" method="post">
			'.$hidden.'
			</form>
			</body>
			</html>
		';
        print $tmp;
        ob_end_flush();
        exit;
    }

    static function changePickUpTwitter()
    {
        global $sql;
        $table = "twitter_ranks";

        $update = $sql->setData($table, null, "pick_up", Globals::get('pick_up'));
        $where = $sql->setWhere($table, null, "tweet_id", "=", Globals::get('id'));
        $sql->updateRecordWhere($table, $update, $where);

        $data["state"] = Globals::get('id');
        jsonEncode($data);
    }

    static function downloadCsvSaleCampaign()
    {
        ini_set('memory_limit', '200m');
        global $sql;

        $query = sprintf('SELECT
                                    `user`.`name` AS "user_name",
                                    `item`.`name` AS "item_name",
                                    `master_item_type_sub`.`name` AS "color",
                                    `item_campaign_sales`.`item_row`,
                                    `item_campaign_sales`.`price` 
                                FROM
                                    item_campaign_sales
                                    INNER JOIN `user` ON item_campaign_sales.`user` = `user`.id
                                    INNER JOIN `item` ON item_campaign_sales.`item` = `item`.id
                                    INNER JOIN `master_item_type_sub` ON item_campaign_sales.`item_type_sub` = `master_item_type_sub`.id 
                                WHERE
                                    item_campaign_sales.user_type = %s ', Globals::get('user_type'));

        if (!empty(Globals::get('user'))) {
            $query .= sprintf('AND item_campaign_sales.`user` = "%s"', Globals::get('user'));
        }

        $result = $sql->rawQuery($query);
        $filename = Globals::get('campaign') . "_" . date("Y_m_d_H_i_s") . ".csv";
        $contents = '"芸人名","アイテム名","カラー","販売数","販売金額"' . "\n";
        while ($rec = $sql->sql_fetch_assoc($result)) {

            $contents .= '"' . $rec['user_name'] . '","' . $rec['item_name'] . '","' . $rec['color'] .
                '","' . $rec['item_row'] . '","' . number_format($rec['item_row'] * $rec['price']) . '"';
            $contents .= "\n";
        }

        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $contents;

        ob_end_flush();
        exit;
    }

    static function get_statistics()
    {
        global $sql;

        jsonEncode($sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT * FROM statistics WHERE date = "%s";', date('Y-m-d')))));
    }

    public function loadMoreCbox(){
        global $sql;
        $ranks = ['no_1', 'no_2', 'no_3'];
        $itemRank = [];
        $tmp = '';
        $arr_print_method = [];

        if (Globals::get('site') == 'item-detail'){
            $condition = getItemTypeQuery();
            $where = $condition['where'];
            $select = $condition['select'];
            $innerJoin = $condition['innerJoin'];
            $groupBy = $condition['groupBy'];
            $orderBy = $condition['orderBy'];
            $query = $select . $innerJoin . $where . $groupBy . $orderBy;
            $data = $sql->rawQuery($query);
            $items = $sql->rawQuery('SELECT * FROM item_ranking');
            while ($rec = $sql->sql_fetch_assoc($items)) {
                foreach ($ranks as $rank) {
                    $itemRank[] = $rec[$rank];
                }
            }
            $print_methods = $sql->rawQuery('SELECT * FROM print_method');
            while ($print_method = $sql->sql_fetch_assoc($print_methods)) {
                $arr_print_method[$print_method['id']] = $print_method['title'];
            }
        } else if (Globals::get('site') == 'news'){
            $clume = $sql->setClume('post_new',null,'title');
            $clume = $sql->setClume('post_new',$clume,'id');
            $clume = $sql->setClume('post_new',$clume,'profile_image');
            $clume = $sql->setClume('post_new_category',$clume,'name');
            $where = $sql->setWhere('post_new',null,'public','=',1);
            if (!empty(Globals::get('category'))){
                $where = $sql->setWhere('post_new',$where,'post_category_id','=',Globals::get('category'));
            }
            if (!empty(Globals::get('name')) && Globals::get('type') == 'post_new_category'){
                $where = $sql->setWhere('post_new_category',$where,'name','=',Globals::get('name'));
            }
            $where = $sql->setWhere('post_new_category',$where,'state','=',1);
            $innerJoin = $sql->setInnerJoin('post_new_category','post_new_category','id','post_new','post_category_id');
            $data = $sql->getSelectResult('post_new',$where,null,null,$clume,null,$innerJoin);
        }

        $rows = $data->num_rows;
        $i = 0;
        $nextPage = Globals::get('page');
        $previousPage = $nextPage - 1;
        $item = Globals::get('item');
        $flag = true;

        while ($result = $sql->sql_fetch_assoc($data)){
            $sale_tag = '';
            $print_method = '';
            if ($result['order_suspended'] == 1) {
                $sale_tag = '<span class="sale_tag order-suspend">注文停止中</span>';
            }else {
                if($result['sales_status'] == 1) {
                    $sale_tag = '<span class="sale_tag">SALE</span>';
                }elseif ($result['sales_status'] == 2) {
                    $sale_tag = '<span class="sale_tag new_tag">NEW</span>';
                }
            }
            if (!empty($result['print_method'])) {
                $print_method = '<br><span class="sub-category"> ' . '$result["print_method"]' . '</span>';
            }

            $i += 1;
            if ($i > $rows){
                $flag = false;
                break;
            }
            if ($i == $rows){
                $flag = false;
            }
            if ($i > $nextPage * $item){
                break;
            }
            if($i < $previousPage * $item + 1){
                continue;
            }
            if (!empty($result['print_method_id'])) {
                $print_method = '<br><span class="sub-category">' . $arr_print_method[$result['print_method_id']] . '</span>';
            }
            if (in_array($result['id'], $itemRank)){
                $ranking = '<div class="crown"><img src="/common/img/toppage/ic-crown-yellow.png" alt="icon"></div>';
            }else {
                $ranking = '';
            }
            if (Globals::get('site') == 'item-detail') {
                $c = array();
                $c[2] = $result['id'];
                $numOfColor = Extension::numOfColor($c);
                $tmp .= '<li class="fadeUpAnime">
                   <a href="/item-detail/' . $result['name'] . '/' . $result['id'] . '">
                        <div class="img-container">
                            <img class="lazyload" src="' . $result['preview_image'] . '">
                            ' . $sale_tag . $ranking . '
                        </div>
                        <div class="description">
                            <h3 class="name">' . $result['name'] . '</h3>
                            <p class="sub-category">' . $result['title'] . '<br>
                    <span class="sub-category">全' . $numOfColor . '色</span>' . $print_method . '                 
                    </p>';

                if ($result['tool_price'] != 0) {
                    $price = number_format($result['tool_price'] * 1.1);
                    $tmp .= '<p class="price">' . number_format($result['tool_price']) . '<span style="font-size: 0.9rem">円 </span><span class="text-tax">(税込 ' . $price . '円)</span></p>';
                } else {
                    $price = number_format($result['item_price'] * 1.1);
                    $tmp .= '<p class="price">' . number_format($result['item_price']) . '<span style="font-size: 0.9rem">円 </span><span class="text-tax">(税込 ' . $price . '円)</span></p>';
                }

                $tmp .= '</div></a></li>';
            } else if (Globals::get('site') == 'news'){
                $tmp .= '<div class="item-list-news-n">
                        <a href="/news/'.$result['id'].'/'.$result['title'].'">
                            <div class="thumbnail">
                                <img src="'.$result['profile_image'].'" alt="thumbnails">
                                <span class="type_tag">'.$result['name'].'</span>
                            </div>
                            <div class="description">
                                <h3>'.$result['title'].'</h3>
                            </div>
                        </a>
                    </div>';
            }
        }

        $content['content'] = $tmp;
        $content['flag'] = $flag;

        echo json_encode($content);
    }


    function changeFaqItemPageState(){
        global $sql;

        $data = array();
        $data["state"] = -1;

        if(!$id = Globals::get("id")) jsonEncode($data);
        if(!$table = Globals::get("type")) jsonEncode($data);

        $state = Globals::get("state");
        if(!is_numeric($state)) jsonEncode($data);

        changeFaqItemPageState($table, $id, $state);

        $data["state"] = $state + 0;
        jsonEncode($data);
    }

    function deleteBanner(){
        global $sql;
        $msg = '削除しました！';
        $id = Globals::get('id');
        $sql->deleteRecord('banner',$id);

        jsonEncode($msg);
    }

    function changeCate(){
        $i = 1;
        global $cc;
        global $sql;
        $ranks = ['no_1', 'no_2', 'no_3'];
        $itemRank = [];
        $num = Globals::get('item');
        $tmp = array();
        $arr_print_method = [];

        if (Globals::get("site") == 'item-detail') {
            $data = getItemType();
            $tmp = array();
            $folder = 'master_item_type';
            $items = $sql->rawQuery('SELECT * FROM item_ranking');
            while ($rec = $sql->sql_fetch_assoc($items)) {
                foreach ($ranks as $rank) {
                    $itemRank[] = $rec[$rank];
                }
            }
            $print_methods = $sql->rawQuery('SELECT * FROM print_method');
            while ($print_method = $sql->sql_fetch_assoc($print_methods)) {
                $arr_print_method[$print_method['id']] = $print_method['title'];
            }
        } else if (Globals::get("site") == 'news'){
            $table = 'post_new';
            $clume = $sql->setClume($table,null,'title');
            $clume = $sql->setClume($table,$clume,'id');
            $clume = $sql->setClume($table,$clume,'profile_image');
            $clume = $sql->setClume('post_new_category',$clume,'name');
            $where = $sql->setWhere($table,null,'public','=',1);
            $where = $sql->setWhere($table,$where,'post_category_id','=',Globals::get('category'));
            $where = $sql->setWhere('post_new_category',$where,'state','=',1);
            $innerJoin = $sql->setInnerJoin('post_new_category','post_new_category','id',$table,'post_category_id');
            $result = $sql->getSelectResult($table,$where,null,null,$clume,null,$innerJoin);
            while ($new = $sql->sql_fetch_assoc($result)){
                $data[] = $new;
            }
            $folder = 'post_new';
        }

        if (empty($data)){
            $tmp['content'] .= '<div style="width: 100%; text-align: center">該当するものを見つけません。</div>';
            $tmp['flag'] = 0;
        } else {
            $template = SystemUtil::getPartsTemplate($folder, 'list');
            foreach ($data as $value1) {
                if (in_array($value1['id'], $itemRank)){
                    $value1['ranking'] = 1;
                }else {
                    $value1['ranking'] = 0;
                }
                if (!empty($value1['print_method_id'])) {
                    $value1['print_method'] = $arr_print_method[$value1['print_method_id']];
                }
                $tmp['content'] .= $cc->run($template, $value1);
                if ($i == $num) {
                    break;
                }
                $i++;
            }
            if ($num < count($data)){
                $tmp['flag'] = 1;
            } else {
                $tmp['flag'] = 0;
            }
        }

        jsonEncode($tmp);
    }

    static function update_item_ranking()
    {
        global $sql;
        $table = 'item_ranking';
        $items = Globals::post();

        if (count($items)) {
            foreach ($items['no_1'] as $i => $value) {
                $data = $sql->setData($table, null, 'no_1', $value);
                $data = $sql->setData($table, $data, 'no_2', $items['no_2'][$i]);
                $data = $sql->setData($table, $data, 'no_3', $items['no_3'][$i]);
                $data = $sql->setData($table, $data, 'edit_unix', time());
                $sql->updateRecord($table, $data, $i);
            }
        }
        HttpUtil::location('/search.php?type=item_ranking');
    }

    function changeRanking(){
        global $cc;
        $category_id = Globals::get('ctagory_id');
        $items = getItemRanking($category_id);
        $tmp = '';
        $template = SystemUtil::getPartsTemplate('master_item_type', 'ranking');
        foreach ($items as $key => $item) {
            $item['item_ranking'] = $key;
            $tmp .= $cc->run($template, $item);
        }
        jsonEncode($tmp);
    }

    function changeMasterCategoryTopPage() {
        global $sql;
        $table = 'master_categories';
        $list = explode("/", Globals::get('list'));
        $state = Globals::get('state');
        if($list) {
            $where = $sql->setWhere($table,null,'id','IN',$list);
            $update = $sql->setData($table, null, 'is_top_page', $state);
            $sql->updateRecordWhere($table, $update, $where);
            jsonEncode(true);
        }
        jsonEncode(false);
    }
    function changeTopPageOrder() {
        global $sql;
        $table = 'master_categories';
        $id = Globals::get('id');
        $order = Globals::get('order');
        if($id) {
            $update = $sql->setData($table, null, 'top_page_order', $order);
            $sql->updateRecord($table, $update, $id);
            jsonEncode(true);
        }
        jsonEncode(false);
    }

    static function downloadCsvGoogleProductCategory()
    {
        global $sql;

        $filename = "掲載中アイテムCSVダウンロード" . date("Y_m_d") . ".csv";

        $contents     = 'id' . "," . 'title' . "," . 'description' . "," . 'google product category' . "," . 'product type' . "," . 'link' . "," . 'image link' . "," . 'additional_image_linkcondition' . "," . 'condition' . "," . 'availability' . "," . 'price' . "," . 'sale price' . "," . 'sale price effective date' . "," . 'gtin' . "," . 'brand' . "," . 'mpn' . "," . 'item group id' . "," . 'gender' . "," . 'age group' . "," . 'color' . "," . 'size' . "," . 'shipping' . "," . 'shipping weight' . "\n";

        $table = "master_item_type";
        $typePage = "master_item_type_page";

        $clume = $sql->setClume($table,null,"id");
        $clume = $sql->setClume($table,$clume,"name");
        $clume = $sql->setClume($table,$clume,"tool_price");
        $clume = $sql->setClume($table,$clume,"maker");
        $clume = $sql->setClume($table,$clume,"size");
        $clume = $sql->setClume($table,$clume,"category_id");
        $clume = $sql->setClume($typePage,$clume,"item_text");
        $clume = $sql->setClume($typePage,$clume,"item_text_detail");
        $clume = $sql->setClume($typePage,$clume,"preview_image");
        $clume = $sql->setClume($typePage,$clume,"preview_image2");
        $clume = $sql->setClume($typePage,$clume,"preview_image3");

        $where = $sql->setWhere($table, null, "state", "=", 1);
        $innerJoin = $sql->setInnerJoin($typePage, $table,'id',$typePage,'item_type');

        $result = $sql->getSelectResult($table, $where, null, null, $clume, null, $innerJoin);

        $title1 = '【1個からプリント】';
        $description1 = '1個からフルカラープリントできて4営業日発送できる！';
        $condition = 'new';
        $availability = 'in_stock';
        $gender = 'unisex';
        $ageGroup = 'adult';

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $c[2] = $rec['id'];
            $numOfColor = Extension::numOfColor($c);
            $title = $title1 .  rtrim($rec['name']);
            $description = $description1 .  rtrim($rec['item_text']) .  rtrim($rec['item_text_detail']);
            $googleCategory = !empty(constants::GOOGLE_PRODUCT_CATEGORY[$rec['category_id']]) ? constants::GOOGLE_PRODUCT_CATEGORY[$rec['category_id']] : "";
            $link = 'https://ondemand.cbox.nu/item-detail/' . $rec['id'];
            $preview_image = !empty($rec['preview_image3']) ?  rtrim($rec['preview_image3']) :  rtrim($rec['preview_image2']);
            $preview_image = !empty($preview_image) ? $preview_image :  rtrim($rec['preview_image']);
            $price = $rec['tool_price'] * 1.1;

            $contents .= '"' . $rec['id'] . '"' . "," . '"' . $title . '"' . "," . '"' . $description . '"' . "," . '"' . $googleCategory . '"' . "," . '""' . "," . '"' . $link . '"' . "," . '"' . $rec['preview_image'] . '"' . "," . '"' . $preview_image . '"' . "," . '"' . $condition . '"' . "," . '"' . $availability . '"' . "," . '"' . $price . 'JPY' . '"' . "," . '""' . "," . '""' . "," . '""' . "," . '"' . $rec['maker'] . '"' . "," . '""' . "," . '""' . "," . '"' . $gender . '"' . "," . '"' . $ageGroup . '"' . "," . '"全' . $numOfColor . '色"' . "," . '"' . $rec['size'] . '"' . "," . '""' . "," . '""';
            $contents .= "\n";
        }

        $contents = mb_convert_encoding($contents, "SHIFT_JIS", "UTF-8");

        ob_end_clean();
        ob_start();

        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo $contents;

        ob_end_flush();
        exit;
        //HttpUtil::download($filename, $contents);
    }

    function getCategoryTopPage(){
        list($count, $tmp) = getMasterCategory(100, 10);
        jsonEncode($tmp);
    }
}
