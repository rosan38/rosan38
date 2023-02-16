<?php

require 'vendor/autoload.php';
require_once 'config/apple_pay/apple_pay_conf.php';
require_once 'include/cc/RandomStar.php';

class ccDraw
{
    const CATEGORY_LIMIT = 1;
    const TYPE_RANK_MAX = 9;

    static function drawGodLoginHeader($c, $data, &$draw)
    {
        global $cc;

        if (Globals::session("GOD_LOGIN_TYPE") && Globals::session("GOD_LOGIN_ID")) {
            $cc->setVariable("GOD_LOGIN_TYPE", Globals::session("GOD_LOGIN_TYPE"));
            $cc->setVariable("GOD_LOGIN_ID", Globals::session("GOD_LOGIN_ID"));
            return $cc->run(SystemUtil::getPartsTemplate("system", "godlogin"));
        }
        return;
    }

    static function drawDateInput($c, $data)
    {
        return '<input type="date" name="' . $c[2] . '" value="' . Globals::get('created_at') . '"/>';
    }

    static function drawDateInput2($c, $data)
    {
        $date = null;
        if ($data[$c[2]]) $date = date_format(date_create($data[$c[2]]), "Y-m-d");
        return '<input type="date" name="' . $c[2] . '" value="' . $date . '"/>';
    }

    static function drawSelectGroup($c)
    {
        global $sql;

        $clume = $sql->setClume($c[3], null, 'id');
        $clume = $sql->setClume($c[3], $clume, 'name');
        $faq_group = $sql->getSelectResult($c[3], null, null, null, $clume);

        $tmp = '<select name="' . $c[2] . '">';
        $tmp .= '<option value="">全てのアイテム</option>';
        while ($rec = $sql->sql_fetch_assoc($faq_group)) {
            if (Globals::get($c[2]) == $rec['id']) $tmp .= '<option value="' . $rec['id'] . '" selected>' . $rec['name'] . '</option>';
            else $tmp .= '<option value="' . $rec['id'] . '">' . $rec['name'] . '</option>';
        }
        $tmp .= '</select>';
        return $tmp;
    }

    static function drawParts($c, $data, &$draw)
    {
        global $cc;

        if (isset($c[4]) && $c[4]) return $cc->run(SystemUtil::getPartsTemplate($c[2], $c[3], $c[4]));
        else if (isset($c[3]) && $c[3]) return $cc->run(SystemUtil::getPartsTemplate($c[2], $c[3]));
        else if (isset($c[2]) && $c[2]) return $cc->run(SystemUtil::getPartsTemplate($c[2]));
        else return;
    }

    static function unHeader($c, $data, &$draw)
    {
        global $DRAW_HEAD_PARTS;

        if (isset($c[2]) && ($c[2] == "false" || $c[2] == "FALSE"))
            $DRAW_HEAD_PARTS = true;
        else
            $DRAW_HEAD_PARTS = false;
    }

    static function unFooter($c, $data, &$draw)
    {
        global $DRAW_FOOT_PARTS;

        if (isset($c[2]) && ($c[2] == "false" || $c[2] == "FALSE"))
            $DRAW_FOOT_PARTS = true;
        else
            $DRAW_FOOT_PARTS = false;
    }

    static function setHeadTitle($c, $data, &$draw)
    {
        $draw->setHeadTitle(implode(" ", array_slice($c, 2)));
    }

    static function resetPdLink()
    {
        return Globals::get('back');
    }

    /**
     * Draw the email
     *
     * @return string
     */
    static function drawEmail()
    {
        return Globals::post('mail');
    }

    static function getHeadTitle($c, $data, &$draw)
    {
        return $draw->getHeadTitle();
    }

    static function drawError($c, $data, &$draw)
    {
        $error = $draw->getErrorMessage();
        if (is_array($error)) {
            if (isset($c[2]) && $c[2] && isset($error[$c[2]])) return $error[$c[2]];
            return;
        }
        if (preg_match('/error-step-2/', $error)) {
            return;
        }
//        ペイアウトエラーの表示
        if (!empty(Globals::get('error_message_rakuten'))) {
            $error = '<div class="error"><p class="msg">支払支払楽天支払</p><ul><li>支払方法楽天支払のエラー(' . Globals::get('error_message_rakuten') . ')</li></ul></div>';

        }
        if (!empty(Globals::get('error_message_pay'))) {
            $error = '<div class="error"><p class="msg">支払支払PayPay</p><ul><li>支払方PayPay払のエラー(' . Globals::get('error_message_pay') . ')</li></ul></div>';

        }
        return $error;
    }

    static function drawToken($c, $data, &$draw)
    {
        if (isset($c[2]) && $c[2] && isset($c[3]) && $c[3])
            $tc = SystemUtil::setTokenCode($c[2], $c[3]);
        else
            $tc = SystemUtil::setTokenCode(basename($_SERVER["SCRIPT_NAME"], '.php'), Globals::get("type"));

        return '<input type="hidden" name="TOKEN_CODE" value="' . h($tc) . '" />' . "\n";
    }

    static function drawListCategoryStyle($c, $data)
    {
        global $sql;
        $content = '';
        $query = "SELECT * FROM master_category_styles ORDER BY sort_index ASC";
        $list_category_style = $sql->rawQuery($query);
        if ($list_category_style->num_rows) {
            $active = 'active';
            $selected = 'true';
            while ($category_style_rec = $sql->sql_fetch_assoc($list_category_style)) {
                $id = $category_style_rec['id'];
                $content .= "<li class='nav-item btn-category-style' data-category-style-id='$id'>
                                <a class='nav-link category $active' id='cat$id-tab' data-toggle='tab' href='#cat$id' role='tab'
                                    aria-controls='#cat$id' aria-selected='$selected'>{$category_style_rec['title']}</a>
                             </li>";
                $active = '';
                $selected = 'false';
            }
        }

        return $content;
    }

    static function drawListTagItem($c, $data)
    {
        global $sql;
        $table = 'master_item_tag';
        $list_tag_id_item = array();
        $list_tag_name_item = array();

        if (isset($c[2]) && $c[2] == 'show') {
            if (empty($data['tag_market'])) {
                $where = $sql->setWhere($table, null, 'item_id', '=', $data['id']);
                if (isset($c[3]) && $c[3] == 'info' && !empty($data[''])) {
                    $where = $sql->setWhere($table, null, 'item_id', '=', $data['parent_color_change']);
                }
                $order = $sql->setOrder($table, null, 'id', 'ASC');
                $row = $sql->getRow($table, $where);

                if ($row) {
                    $list_item_tag = $sql->getSelectResult($table, $where, $order);

                    while ($item_tag = $sql->sql_fetch_assoc($list_item_tag)) {
                        $tag_market_rec = $sql->selectRecord('master_tag', $item_tag['tag_id']);
                        if ($tag_market_rec) {
                            $list_tag_id_item[] = $item_tag['tag_id'];
                            $list_tag_name_item[] = $tag_market_rec['name'];
                        }
                    }
                }
            } else {
                $list_tag_id_item = $data['tag_market'];
            }

            if (isset($c[3]) && $c[3] == 'info') {
                $content = '';
                foreach ($list_tag_name_item as $key => $value) {
                    $url_tag = urlencode($value);
                    global $MARKET_SERVICE_HOST;
                    is_ssl() ? $domain = 'https://' . $MARKET_SERVICE_HOST : $domain = 'http://' . $MARKET_SERVICE_HOST;
                    $content .= "<a href='{$domain}/item_tag/{$url_tag}'>#{$value}</a>, ";
                }

                $content = rtrim($content, ", ");
            } else {
                $content = "<select style='width: 100%;' name='tag_market[]' multiple id='select_tag'>";
                $list_tag_market = $sql->getSelectResult('master_tag');
                if (!$list_tag_market->num_rows) {
                    return "no data";
                }
                while ($tag_market = $sql->sql_fetch_assoc($list_tag_market)) {
                    if (in_array($tag_market['id'], $list_tag_id_item)) {
                        $content .= "<option value='{$tag_market['id']}' selected>{$tag_market['name']}</option>";
                    } else {
                        $content .= "<option value='{$tag_market['id']}'>{$tag_market['name']}</option>";
                    }
                }

                $content .= "</select>";
            }
        } else if (isset($c[2]) && $c[2] == 'check') {
            $content = '';
            if (!empty($data['tag_market'])) {
                foreach ($data['tag_market'] as $key => $value) {
                    $tag_market_rec = $sql->selectRecord('tag_market', $value);
                    $content .= "#{$tag_market_rec['name']}, ";
                }
            }

            $content = rtrim($content, ", ");
        }

        return $content;
    }

    static function drawListCategoryAddItem($c, $data)
    {
        global $sql;
        $content = '';
        $query = "SELECT * FROM master_category_styles ORDER BY sort_index ASC";
        $list_category_style = $sql->rawQuery($query);
        if ($list_category_style->num_rows) {
            $show = 'show';
            $active = 'active';
            while ($category_style_rec = $sql->sql_fetch_assoc($list_category_style)) {
                $category_style_id = $category_style_rec['id'];
                $content .= "<div class='content tab-pane fade $show $active' id='cat$category_style_id' role='tabpanel' aria-labelledby='cat$category_style_id-tab'>";
                $query_category = "SELECT * FROM master_categories WHERE is_deleted = 0 AND master_category_style_id = '{$category_style_id}' ORDER BY master_categories.`order`";
                $list_category = $sql->sql_query($query_category);
                if ($list_category->num_rows) {
                    while ($category_rec = $sql->sql_fetch_assoc($list_category)) {
                        $content .= "<div class='product-card category-card' data-category-id='{$category_rec['id']}'>
                                        <div class='title-header'>
                                            <div class='title'>{$category_rec['title']}</div>
                                        </div>
                                        <img class='preview'
                                        src='{$category_rec['image_url']}'
                                        alt='{$category_rec['title']}'>
                                        <button class='action-button'>選択</button>
                                    </div>";
                    }
                }
                $content .= "</div>";
                $show = '';
                $active = '';
            }
        }

        return $content;
    }

    static function drawHiddenID($c, $data, &$draw)
    {
        global $sql;

        if (!$data) return null;
        if (!isset($data["id"])) return null;
        return '<input type="hidden" name="id" value="' . h($data["id"]) . '" />' . "\n";
    }

    static function drawImagePreviewBlankItem($c, $data)
    {
        global $sql;
        if (Globals::get('type') == 'pay_item') {
            $payItemId = Globals::get('id');
        } else {
            $payItemId = Globals::get('pay_item_id');
        }

        $payItem = $sql->keySelectRecord('pay_item', 'id', $payItemId);

        if ($payItem) {
            return '<img class="lazyload" data-src="' . $payItem['item_image'] . '" width="200" border="0"/>';
        } else {
            return '<img class="lazyload" data-src="' . $data['item_preview1'] . '" width="200" border="0"/>';
        }
    }

    static function drawHiddenStep($c, $data, &$draw)
    {
        global $sql;

        if ($storeId = Globals::post('store_id')) {
            $data['store_id'] = $storeId;
        }

        if (Globals::post("step")) {
            $step = Globals::post("step") + 1;
        } else {
            $step = 1;
        }

        $ret = '<input type="hidden" name="step" value="' . $step . '" />' . "\n";
        $ret .= '<input type="hidden" name="page" value="' . $c[2] . '" />' . "\n";

        if (!$data) return $ret;

        $none_hidden = array_slice($c, 3);
        $data['order_from_app'] = Globals::session("design_from");
        foreach ($data as $key => $val) {
            //指定項目はhiddenしない
            if (in_array($key, $none_hidden)) continue;

            if (is_array($val))
                for ($i = 0; $i < count($val); $i++) {
                    $ret .= '<input type="hidden" name="' . $key . '[]" value="' . h($val[$i]) . '" />' . "\n";
                }
            else
                $ret .= '<input type="hidden" name="' . $key . '" value="' . h($val) . '" />' . "\n";
        }
        if (!empty(Globals::post('answer_question_1'))) {
            $ret .= '<input type="hidden" name="answer_question_1" value="' . Globals::post('answer_question_1') . '" />' . "\n";
        }

        if (!empty(Globals::post('pay_type'))) {
            $ret .= sprintf('<span id="previous_pay_type" data-value="%s" style="display: none;"></span>', Globals::post('pay_type'));
        }

        return $ret;
    }

    static function drawHidden($c, $data, &$draw)
    {
        global $sql;

        if (!$data) return null;

        $ret = "";
        foreach ($data as $key => $val) {
            if (is_array($val))
                for ($i = 0; $i < count($val); $i++) {
                    $ret .= '<input type="hidden" name="' . $key . '[]" value="' . h($val[$i]) . '" />' . "\n";
                }
            else
                $ret .= '<input type="hidden" name="' . $key . '" value="' . h($val) . '" />' . "\n";
        }
        return $ret;
    }

    /**
     * Draw date for search shipped orders
     *
     * @return null|string
     */
    static function drawDate()
    {
        $date = '';
        $date_y = Globals::get("y");
        $date_m = Globals::get("m");
        $date_d = Globals::get("d");

        if ($date_y) {
            $date = $date_y;
        }

        if ($date_m) {
            $date .= "-";
            $date .= $date_m < 10 && strlen($date_m) == 1 ? "0{$date_m}" : $date_m;
        }

        if ($date_d) {
            $date .= "-";
            $date .= $date_d < 10 && strlen($date_d) == 1 ? "0{$date_d}" : $date_d;
        }

        return $date;
    }

    static function administratorName()
    {
        if (!empty(Globals::session('LOGIN_NAME'))) {
            return Globals::session('LOGIN_NAME');
        }

        return '管理者 様';
    }

    /**
     * Draw pay point
     *
     * @param $c
     * @param $data
     * @return float|int|mixed|null
     */
    static function drawUpPoint($c, $data)
    {
        if (!isset($data['point'])) {
            $id = Globals::session("LOGIN_ID");
            $type = Globals::session("LOGIN_TYPE");
            $data['point'] = 0;

            if (!$id || !$type || $type == "nobody") {
                $data['point'] = 0;
            } else {
                global $sql;

                $rec = $sql->selectRecord($type, $id);

                if ($rec) {
                    $data['point'] = $rec['point'];
                }
            }
        }

        $total = getUpPoint();

        if ($data['point'] >= $total) {
            return $total;
        } else {
            return $data['point'];
        }
    }

    static function drawLogInId($c, $data, &$draw)
    {
        global $cc;

        if (Globals::session("LOGIN_ID")) {
            $data['user_login'] = Globals::session("LOGIN_ID");
            return $data['user_login'];
        }
        return null;
    }

    static function drawNoteMessage($c, $data)
    {
        return showNoteMessage($data['note_id']);
    }

    static function drawNoteTitle($c, $data)
    {
        return showNoteTitle($data['note_id']);
    }

    static function drawNoteId($c, $data)
    {
        if ($data['note_id']) {
            $note_id = $data['note_id'];
        } else {
            $note_id = Globals::get('note_id');
        }

        return $note_id;
    }

    static function drawListNote($c, $data)
    {
        $list_note = showListNote();

        $note_id = self::drawNoteId($c, $data);

        foreach ($list_note as $note) {
            if ($note_id != $note['id']) {
                $tmp[] = '<option value="' . $note['id'] . '">' . $note['note_title'] . '</option>';
            } else {
                $tmp[] = '<option selected=\'selected\' value="' . $note['id'] . '">' . $note['note_title'] . '</option>';
            }
        }

        $option = implode("</br> ", $tmp);

        return $option;
    }

    static function drawListSizeAndSubBlankItem($c)
    {

        if (!empty($c['color']) && empty($c['size'])) {

            $item = getListBlankItem($c[2], $c['color'], null);
        } elseif (empty($c['color']) && !empty($c['size'])) {

            $item = getListBlankItem($c[2], null, $c['size']);
        } else {

            $item = getListBlankItem($c[2]);
        }

        $tmp = '<thead>';
//    	$countMaxSize = 0;

        if (!$item['list_item']) {
            return null;
        }

//    	foreach ($item['list_item'] as $itemTypeSub) {
//    		$countSize = count($itemTypeSub);
//			if($countMaxSize < $countSize) $countMaxSize = $countSize;
//		}

        $tmp .= "<th style='position: sticky; left: 0; top: 0; z-index: 2'></th>";

        foreach ($item['list_size'] as $size) {
            $tmp .= "<th style='position: sticky; top: 0'>" . $size . "</th>";
        }
        $text = getTextNumberBlank($c[2]);
        $tmp .= '</thead>';
        foreach ($item['list_item'] as $itemTypeSub) {
            $tmp .= "<tr class='even'>";
            $i = 0;
            foreach ($item['list_size'] as $size) {
                if (isset($itemTypeSub[$size])) {
                    if ($i == 0) {
                        $tmp .= "<th style='position: sticky; left: 0'>" . $itemTypeSub[$size]['item_type_sub_name'] . "</th>";
                        if ($itemTypeSub[$size]['stock'] > 0) {
                            $tmp .= "<td>" . $itemTypeSub[$size]['stock'] . $text . "</td>";
                        } else {
                            if ($itemTypeSub[$size]['expected_import_date'] != null) {
                                if (strtotime($itemTypeSub[$size]['expected_import_date']) < 0) {
                                    $tmp .= "<td>入荷未定</td>";
                                } else {
                                    $dateUnix = strtotime($itemTypeSub[$size]['expected_import_date']);
                                    $tmp .= sprintf('<td>%s月%s日入荷予定</td>', date("m", $dateUnix), date("d", $dateUnix));
                                }
                            } else {
                                $tmp .= "<td>未定</td>";
                            }
                        }
                    } else {
                        if ($itemTypeSub[$size]['stock'] > 0) {
                            $tmp .= "<td>" . $itemTypeSub[$size]['stock'] . $text . "</td>";
                        } else {
                            if ($itemTypeSub[$size]['expected_import_date'] != null) {
                                if (strtotime($itemTypeSub[$size]['expected_import_date']) < 0) {
                                    $tmp .= "<td>入荷未定</td>";
                                } else {
                                    $dateUnix = strtotime($itemTypeSub[$size]['expected_import_date']);
                                    $tmp .= sprintf('<td>%s月%s日入荷予定</td>', date("m", $dateUnix), date("d", $dateUnix));
                                }
                            } else {
                                $tmp .= "<td>未定</td>";
                            }
                        }
                    }
                } else {
                    if ($i == 0) {
                        $tmp .= "<th style='position: sticky; left: 0'>" . $itemTypeSub[array_keys($itemTypeSub)[0]]['item_type_sub_name'] . "</th>";
                    }
                    $tmp .= "<td>0</td>";
                }
                $i++;
            }
            $tmp .= "</tr>";
        }

        return $tmp;
    }

    static function drawButtonAddCartBlankItem($c)
    {
        $result = countBlankItem($c[2]);

        if (!empty($c[3]) && $c[3] == 'check') {
            return 1;
        } else {
            if (!$result) {
                return '';
            } else {

                return "<a class='design_btn right'>無地で購入する</a>";
            }
        }
    }

    static function drawNoteContent($c, $data)
    {
        return $data['message'];
    }

    static function drawNormalCatC1($c, $data)
    {
        global $sql;

        $tmp = "";
        $category_1 = $category_2 = $category_3 = null;
        $min_price = number_format(Globals::get('price_A'));
        $max_price = number_format(Globals::get('price_B'));

        for ($i = 1; $i <= 3; $i++) {
            if (!empty($data[sprintf('category_%s', $i)])) {
                ${"category_$i"} = $sql->selectRecord(sprintf('master_item_categories%s', $i), $data[sprintf('category_%s', $i)]);
            }
        }

        if (!empty($min_price) && !empty($max_price)) {
            $tmp .= sprintf('<li><a href="">%s円～%s円</a></li>', $min_price, $max_price);
        } elseif (empty($min_price) && !empty($max_price)) {
            $tmp .= sprintf('<li><a href="">0円～%s円</a></li>', $max_price);
        }

        if (isset($category_1['id'])) {

            if (isset($category_2['id']) && !isset($category_3['id'])) {
                $tmp .= '<li><a href="/search.php?type=item&category_1=' . $category_1['id'] . '&category_2=&flag=1"> ' . $category_1['name'] . '販売一覧</a></li>
						<li><a href="/search.php?type=item&category_1=' . $category_1['id'] . '&category_2=' . $category_2['id'] . '&flag=1"> ' . $category_2['name'] . '販売一覧</a></li>';
            } else if (isset($category_2['id']) && isset($category_3['id'])) {
                $tmp .= '<li><a href="/search.php?type=item&category_1=' . $category_1['id'] . '&category_2=&category_3=&flag=1"> ' . $category_1['name'] . '販売</a></li>
						<li><a href="/search.php?type=item&category_1=' . $category_1['id'] . '&category_2=' . $category_2['id'] . '&category_3=&flag=1"> ' . $category_2['name'] . $category_1['name'] . '販売</a></li>
						<li><a href="/search.php?type=item&category_1=' . $category_1['id'] . '&category_2=' . $category_2['id'] . '&category_3=' . $category_3['id'] . '&flag=1"> ' . $category_3['name'] . $category_1['name'] . '販売一覧</a></li>';
            } else {
                $tmp .= sprintf('<li><a href="/search.php?type=item&category_1=%s&flag=1"> %s販売一覧</a></li>', $category_1['id'], $category_1['name']);
            }
        } else {
            if (isset($category_2['id'])) {
                $tmp .= sprintf('<li><a href="/search.php?type=item&category_2=%s&flag=1"> %s販売一覧</a></li>', $category_2['id'], $category_2['name']);
                if (isset($category_3['id'])) {
                    $tmp .= sprintf('<li><a href="/search.php?type=item&category_2=%s&category_3=%s&flag=1">%s販売一覧</a></li>', $category_2['id'], $category_3['id'], $category_3['name']);
                }
            }
        }

        if (!empty(Globals::get('keyword'))) {
            $tmp .= sprintf('<li><a href="#"> %s販売一覧</a></li>', Globals::get('keyword'), Globals::get('keyword'));
        }
        return $tmp;
    }

    static function drawPolicyStatus($c, $data)
    {
        if ($c[2] == 'list') {
            $list = true;
        } else {
            $list = false;
        }

        return Globals::drawPolicyStatuses(Globals::get('policy_check'), $list);
    }

    static function drawAdminLink()
    {
        if (Globals::session('ADMIN') && Globals::get('admin') == 'true') {
            return 'regist.php?type=pay&admin=true';
        }
    }

    static function drawPendingStatus($c, $data)
    {
        if ($c[2] == 'list') {
            $list = true;
        } else {
            $list = false;
        }

        return Globals::drawPendingStatuses(Globals::get('pending'), $list);
    }

    static function drawGroupSearchStatus($c, $data)
    {
        if (Globals::get("groupSearch")) {
            return Globals::get("groupSearch");
        } else {
            return '';
        }
    }

    static function drawPointInfo($c, $data)
    {
        $value = '';

        if ($c[2] == 'pay_num' || $c[2] == 'table_num') {
            global $sql;

            if ($c[2] == 'table_num') {
                $data['pay_id'] = $data['table_id'];
            }

            $pay = $sql->selectRecord('pay', $data['pay_id']);

            if (!empty($pay)) {
                $value = $pay['pay_num'];
            }
        } elseif (!empty($data[$c[2]]) && ($c[2] == 'created' || $c[2] == 'expiry')) {
            $value = date('Y年m月d日 H:i', strtotime($data[$c[2]]));
        } elseif (!empty($data[$c[2]]) && $c[2] == 'regist_unix') {
            $value = date('Y年m月d日 H:i', $data[$c[2]]);
        } elseif ($c[2] == 'state') {
            $value = UPOINT_STATE['title'][$data[$c[2]]];
        } elseif ($c[2] == 'pay_id' || $c[2] == 'table_id') {
            $value = $data[$c[2]];
        }

        return $value;
    }

    static function drawNameCategory($c, $data)
    {
        global $sql;
        if ($c[2] == 'master_item_categories1') {
            $category = $sql->selectRecord('master_item_categories1', $c[3]);
        }
        if ($c[2] == 'master_item_categories2') {
            $category = $sql->selectRecord('master_item_categories2', $c[3]);
        }
        if ($c[2] == 'master_item_categories3') {
            $category = $sql->selectRecord('master_item_categories3', $c[3]);
        }

        return $category['name'];

    }

    static function drawPageCategory()
    {
        global $sql;
        $table = 'page_category';
        $category1 = Globals::get("category_1");
        $category2 = Globals::get("category_2");
        $category3 = Globals::get("category_3");

        if (empty($category1) && empty($category2) && empty($category3)) {
            $where = $sql->setWhere($table, null, "category_1", "=", '0');
            $where = $sql->setWhere($table, $where, "category_2", "=", '0');
            $where = $sql->setWhere($table, $where, "category_3", "=", '0');
            $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

            return $result;
        } else {
            if (!empty($category1)) {
                $where = $sql->setWhere($table, null, "category_1", "=", $category1);
            } else {
                $where = $sql->setWhere($table, null, "category_1", "=", '0');
            }
            if (!empty($category2)) {
                $where = $sql->setWhere($table, $where, "category_2", "=", $category2);
                if (!empty($category3)) {
                    $where = $sql->setWhere($table, $where, "category_3", "=", $category3);
                } else {
                    $where = $sql->setWhere($table, $where, "id", "IN", '(select id from page_category where category_3 is null)');
                }
            } else {
                $where = $sql->setWhere($table, $where, "id", "IN", '(select id from page_category where category_3 is null and category_2 is null)');
            }
            $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

            return $result;
        }
    }

    static function drawFieldCategory($c, $data)
    {
        $page = self::drawPageCategory();

        switch ($c[2]) {
            case 'image':
                if (!empty($page['image'])) {
                    return '<img class="lazyload" data-src="' . $page['image'] . '" height="100%" width="100%" alt="img-category">';
                } else {
                    return '';
                }
            case 'description':
                if (!empty($page['description'])) {
                    return $page['description'];
                } else {
                    return '';
                }
            case 'title':
                if (!empty($page['title'])) {
                    return $page['title'];
                } else {
                    return '';
                }
            default:
                return 'select field';
        }
    }

    static function drawNewItemPage()
    {
        global $sql;
        global $search;

        $table = "item";
        $category1 = Globals::get("category_1");
        $category2 = Globals::get("category_2");
        $category3 = Globals::get("category_3");

        $where = null;
        $order = $sql->setOrder($table, null, "regist_unix", "DESC");
        $where = $sql->setWhere($table, $where, "regist_unix", ">", "0");
        $where = $sql->setWhere($table, $where, "state", "=", 1);
        $where = $sql->setWhere($table, $where, "user", "IN", "(SELECT id FROM user WHERE state=1)");
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);

        if (empty($category1) && empty($category2) && empty($category3)) {
            $result = $sql->getSelectResult($table, $where, $order, array(0, 5));
            return ccSearch::drawList(null, null, $search, $result, true);
        } else {
            if (!empty($category1)) {
                $where = $sql->setWhere($table, $where, "category_1", "=", $category1);
            }
            if (!empty($category2)) {
                $where = $sql->setWhere($table, $where, "category_2", "=", $category2);
                if (!empty($category3)) {
                    $where = $sql->setWhere($table, $where, "category_3", "=", $category3);
                }
            }
        }
        $result = $sql->getSelectResult($table, $where, $order, array(0, 5));

        return ccSearch::drawList(null, null, $search, $result, true);

    }

    static function drawBestSellerItemPage()
    {
        global $sql;
        global $search;

        $table = "item";
        $category1 = Globals::get("category_1");
        $category2 = Globals::get("category_2");
        $category3 = Globals::get("category_3");

        $where = $sql->setWhere($table, null, "regist_unix", ">", "0");
        $where = $sql->setWhere($table, $where, "state", "=", 1);
        $where = $sql->setWhere($table, $where, "user", "IN", "(SELECT id FROM user WHERE state=1)");
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);

        if (empty($category1) && empty($category2)) {
            $query = '(SELECT item FROM pay_item GROUP BY item ORDER BY sum( item_row ) DESC )';
            $where = $sql->setWhere($table, $where, "id", "IN", $query);

            $result = $sql->getSelectResult($table, $where, '', array(0, 5));

            return ccSearch::drawList(null, null, $search, $result, true);
        } elseif (!empty($category1) && empty($category2)) {
            $query = '(SELECT * FROM ( SELECT item FROM pay_item WHERE item IN ( SELECT id FROM item WHERE category_1 = ' . $category1 . ') GROUP BY item ORDER BY sum( item_row ) DESC LIMIT 0,5 ) AS t)';
        } elseif (empty($category1) && !empty($category2)) {
            if (!empty($category3)) {
                $query = '(SELECT * FROM ( SELECT item FROM pay_item WHERE item IN ( SELECT id FROM item WHERE AND category_2 = ' . $category2 . ' AND category_3 = ' . $category3 . ' ) GROUP BY item ORDER BY sum( item_row ) DESC LIMIT 0,5 ) AS t)';
            } else {
                $query = '(SELECT * FROM ( SELECT item FROM pay_item WHERE item IN ( SELECT id FROM item WHERE category_2 = ' . $category2 . ' ) GROUP BY item ORDER BY sum( item_row ) DESC LIMIT 0,5 ) AS t)';
            }
        } else {
            if (!empty($category3)) {
                $query = '(SELECT * FROM ( SELECT item FROM pay_item WHERE item IN ( SELECT id FROM item WHERE category_1 = ' . $category1 . ' AND category_2 = ' . $category2 . ' AND category_3 = ' . $category3 . ' ) GROUP BY item ORDER BY sum( item_row ) DESC LIMIT 0,5 ) AS t)';
            } else {
                $query = '(SELECT * FROM ( SELECT item FROM pay_item WHERE item IN ( SELECT id FROM item WHERE category_1 = ' . $category1 . ' AND category_2 = ' . $category2 . ' ) GROUP BY item ORDER BY sum( item_row ) DESC LIMIT 0,5 ) AS t)';
            }
        }

        $where = $sql->setWhere($table, $where, "id", "IN", $query);

        $result = $sql->getSelectResult($table, $where);

        return ccSearch::drawList(null, null, $search, $result, true);
    }

    static function drawTitlePageCategory()
    {
        global $sql;

        $category1 = $sql->selectRecord('master_item_categories1', Globals::get("category_1"));
        $category2 = $sql->selectRecord('master_item_categories2', Globals::get("category_2"));
        $category3 = $sql->selectRecord('master_item_categories3', Globals::get("category_3"));

        if (!empty($category1)) {
            if (!empty($category2)) {
                if (!empty($category3)) {
                    return '' . $category3['name'] . '' . $category1['name'] . '';
                } else {
                    return '' . $category2['name'] . '' . $category1['name'] . '';
                }
            } else {
                return '' . $category1['name'] . '';
            }
        } else {
            if (!empty($category2)) {
                if (!empty($category3)) {
                    return '' . $category3['name'] . 'アイテム';
                } else {
                    return '' . $category2['name'] . 'アイテム';
                }
            }
        }
    }

    static function drawListCategory($c, $data)
    {
        global $sql;

        $category_1 = Globals::get("category_1");
        $category_2 = Globals::get("category_2");

        switch ($c[2]) {
            case 'master_item_categories3':
                $where = $sql->setWhere($c[2], null, "parent", "=", Globals::get("category_2"));

                $result = $sql->getSelectResult($c[2], $where);

                while ($rec = $sql->sql_fetch_assoc($result)) {
                    $tmp[] = '<a href="/search.php?type=item&category_1=' . $category_1 . '&category_2=' . $category_2 . '&category_3=' . $rec['id'] . '&flag=1">' . $rec['name'] . '</a>';
                }

                return implode(", ", $tmp);
            case 'master_item_categories2':
                $where = $sql->setWhere($c[2], null, "parent", "=", Globals::get("category_2"));

                $result = $sql->getSelectResult($c[2], $where);

                while ($rec = $sql->sql_fetch_assoc($result)) {
                    $tmp[] = '<a href="/search.php?type=item&category_1=' . $category_1 . '&category_2=' . $rec['id'] . '&flag=1">' . $rec['name'] . '</a>';
                }

                return implode(", ", $tmp);
            default:
                return 'select field';
        }
    }

    static function drawSelectCardSeq()
    {
        global $sql;

        $table = 'card_information';

        $rec = $sql->selectRecord('user', Globals::session("LOGIN_ID"));
        if (!empty($rec)) {
            Globals::setSession("FLAG_MEMBER_ID", "1");
        }

        $where = $sql->setWhere($table, null, "gmo_member_id", "=", $rec['gmo_member_id']);
        $order = $sql->setOrder($table, null, "id", "DESC");

        $row = $sql->getRow($table, $where);

        $result = $sql->getSelectResult($table, $where, $order);

        $tmp[] = '<select id="card_seq" name="card_seq" onchange="searchInfoPay()">';
        if (empty($row)) {
            $tmp[] = '<option value="-1">新しいカードを追加</option>';
        } else {
            $flag = 1;
            $tmp[] = '<option value="-1">新しいカードを追加</option>';
            while ($rec = $sql->sql_fetch_assoc($result)) {
                if ($flag == 1) {
                    $tmp[] = '<option selected value="' . $rec['CardSeq'] . '">' . $rec['card_number'] . ' (' . $rec['expire'] . ')</option>';
                    $flag++;
                } else {
                    $tmp[] = '<option value="' . $rec['CardSeq'] . '">' . $rec['card_number'] . ' (' . $rec['expire'] . ')</option>';
                    $flag++;
                }
            }
        }
        $tmp[] = '</select>';

        return implode("<br>", $tmp);
    }

    static function drawToolLink()
    {
        global $sql;
        $table = 'pay_item';
        $where = $sql->setWhere($table, null, 'pay', '=', Globals::get('id'));

        $pay_item = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

        if (!empty($pay_item)) {
            return sprintf('%s?admin_order_id=%s&pay_id=%s&color_id=%s&model_id=%s&admin_item_id=%s&login_type=admin&design_name=',
                DrawToolConfig::HOST, $pay_item['id'],
                Globals::get('id'), $pay_item['item_type_sub'],
                $pay_item['item_type'], $pay_item['item']);
        }
    }

    static function drawAddTagDsp()
    {
        $home_page = array("item_detail", "market", "guide", "business_customer", "about");
        $other_page = array("discount", "business_customer", "cyokusou", "delivery", "matomete", "point", "urank", "faq", "matomete", "malo_special", "tv_item", "questionnaire", "feature_list", "company", "sitemap", "policy");

        $tag_dsp = '
            <script language=\'JavaScript1.1\' async src=\'//pixel.mathtag.com/event/js?mt_id=%s&mt_adid=222767&mt_exem=&mt_excl=&v1=&v2=&v3=&s1=&s2=&s3=\'></script>
            <!--
            Start of Floodlight Tag: Please do not remove
            Activity name of this tag: %s
            URL of the webpage where the tag is expected to be placed: 
            This tag must be placed between the <body> and </body> tags, as close as possible to the opening tag.
            Creation Date: 03/14/2019
            -->
            <script type="text/javascript">
            var axel = Math.random() + "";
            var a = axel * 10000000000000;
            </script>
            <iframe src="https://9250690.fls.doubleclick.net/activityi;src=9250690;type=invmedia;cat=%s;dc_lat=;dc_rdid=;tag_for_child_directed_treatment=;tfua=;npa=;ord=\' + a + \'?" width="1" height="1" frameborder="0" style="display:none"></iframe>
            <noscript>
            <iframe src="https://9250690.fls.doubleclick.net/activityi;src=9250690;type=invmedia;cat=%s;dc_lat=;dc_rdid=;tag_for_child_directed_treatment=;tfua=;npa=;ord=1?" width="1" height="1" frameborder="0" style="display:none"></iframe>
            </noscript>
            <!-- End of Floodlight Tag: Please do not remove -->
        ';

        $home_tag = sprintf($tag_dsp, '1405613', 'HPG_Original lab_2019-03', 'hpg_o0', 'hpg_o0');
        $item_tag = sprintf($tag_dsp, '1405612', 'LPG_Original lab_2019-03', 'lpg_o0', 'lpg_o0');
        $other_tag = sprintf($tag_dsp, '1405617', 'Other_Original lab_2019-03', 'other0', 'other0');
        $cat_tag = sprintf($tag_dsp, '1405614', 'PRD_Original lab_2019-03', 'prd_o0', 'prd_o0');
        $pay_complete_tag = sprintf($tag_dsp, '1405615', 'ECM_Original lab_2019-03', 'ecm_o0', 'ecm_o0');
        $page = $_SERVER['REQUEST_URI'];
        $p = Globals::get('p');

        if ($page === "/") {
            return $home_tag;
        }
        if (!is_bool(array_search($p, $home_page))) {
            return $home_tag;
        } elseif (!is_bool(array_search($p, $other_page))) {
            return $other_tag;
        } elseif ($p == 'cart' || $page === "/regist.php?type=pay") {
            return $cat_tag;
        } else {
            $tag = $other_tag;
            if (!is_null($p)) {
                $tag = $item_tag;
            }
            if ($page === "/regist.php?type=pay&back=true") {
                $tag = $pay_complete_tag;
            }
            if (!is_bool(strpos($page, 'info.php?type=item'))) {
                $tag = $item_tag;
            }
            return $tag;
        }
    }

    static function drawBottomButton($page = '')
    {
        global $device_path;

        $other_page = array("/regist.php?type=pay&back=true", "/regist.php?type=pay", "/login.php", "/page.php?p=market", "/market", "/page.php?p=business_customer", "/regist.php?type=sendmail", "/search.php?type=pay", "/page.php?p=store_info", "/search.php?type=user_fee", "/view.php?type=report", "/regist.php?type=user", '/regist.php?type=pay&orderFromBase=');
        $other_page_str = array("/edit.php", "designenq", "designqa", "/page.php?p=cart");

        $check = false;

        if (!empty($page[2]) && $page[2] == 'item' || $device_path == 'smart/' && $_SERVER['SCRIPT_NAME'] == '/index.php') {
            $script = '';
        }

        if (!is_string($page) || is_string($page) && empty($page)) {
            $page = $_SERVER['REQUEST_URI'];
        }

        foreach ($other_page_str as $value) {
            if (strpos($page, $value) !== false) {
                $check = true;
                break;
            }
        }

        if (!in_array($page, $other_page) && !$check) {
            if (strpos(Globals::get('id'), 'IT') !== false) {
                $key = 5;
            } else {
                if (strpos($page, 'info.php?type=post_new')) {
                    $key = 100;
                } else {
                    $key = 2;
                }
            }

            $htmlButton = "<div class='link-top2-ouside wrap_outchat'><div id='link-top2'><div class='link_outside_chat'><a href='" . Extension::getDrawToolLink([$key => Globals::get("id")], []) . "' class='button-link-drawer'><span class='top_outside'>簡単3分で完成</span><br><span class='bt_outside'>今すぐ作ってみる</span></a><div class='chat_bot'></div><a href='#' class='link-back-top'>ページトップへ</a> </div> </div> </div>";

            if (!isset($script)) {
                $script = '<input type="hidden" id="display-chat" value="1"/>';
            }

            return $htmlButton . $script;
        }

        return '';
    }

    static function drawCheckAllUserDeliveryOrder()
    {
        $data = Globals::get('id_user_delivery_orders');
        $value = implode("/", $data);
        return '<input name="list_all" type="checkbox" value="' . $value . '" />';
    }

    static function drawOptionDefaultCoupon()
    {
        global $sql;
        $query = "SELECT `id`, `name` FROM master_item_web_categories";
        $template_option = '';
        $results = $sql->rawQuery($query);
        while ($result_tmp = $sql->sql_fetch_assoc($results)) {
            $template_option .= '<option value="' . $result_tmp['id'] . '">' . $result_tmp['name'] . '</option>';
        }
        return $template_option;
    }

    static function drawTagCriteo($c, $data)
    {
        $tag = '';
        $tag_default = '
                    <script type="text/javascript" src="//static.criteo.net/js/ld/ld.js" async="true"></script>
                    <script type="text/javascript">
                        window.criteo_q = window.criteo_q || [];
                        window.criteo_q.push(
                                { event: "setAccount", account: "%s" },
                                { event: "setSiteType", type: "%s" },
                                { event: %s }
                        );
                    </script>
                ';
        $page = $_SERVER['REQUEST_URI'];
        $p = Globals::get('p');
        $account = '61171';

        if ($page == "/" || $page == "/index.php") {
            $tag = sprintf($tag_default, $account, $c[2], '"viewHome"');
        } elseif ($p == "item_detail") {
            $item = 'item: ["IT001", "IT114", "IT003"]';
            $tag = sprintf($tag_default, $account, $c[2], '"viewList", ' . $item);
        } elseif ($p == "cart") {
            if (!empty(Globals::session("CART_ITEM"))) {
                $cart = Globals::session("CART_ITEM");
                $data = array();
                foreach ($cart as $key => $val) {
                    $data[] = sprintf('{ id: "%s", price: %s, quantity: %s}', $val['item_type'], $val['cart_price'], $val['cart_row']);
                }
                $str = 'item: [' . implode(",", $data) . ']';
                $tag = sprintf($tag_default, $account, $c[2], '"viewBasket", ' . $str);
            }
        } elseif (!is_bool(strpos($page, 'info.php?type=item'))) {
            $item = 'item: "MKT1"';
            $tag = sprintf($tag_default, $account, $c[2], '"viewItem", ' . $item);
        } elseif ($page === "/regist.php?type=pay&back=true") {
            $cart = Globals::session("CART_ITEM_CRITEO");
            $data = array();
            foreach ($cart as $key => $val) {
                $data[] = sprintf('{ id: "%s", price: %s, quantity: %s}', $val['item_type'], $val['cart_price'], $val['cart_row']);
            }
            $pay_num = Globals::session('PAY_NUM');
            $str = 'id: ' . '"' . $pay_num . '", item: [' . implode(",", $data) . ']';
            Globals::setSession('PAY_NUM', '');
            Globals::setSession('CART_ITEM_CRITEO', '');
            $tag = sprintf($tag_default, $account, $c[2], '"trackTransaction", ' . $str);
        } elseif (!empty(Globals::get('ITEM_ID_CRITEO'))) {
            $item = 'item: "' . Globals::get('ITEM_ID_CRITEO') . '"';
            $tag = sprintf($tag_default, $account, $c[2], '"viewItem", ' . $item);
            Globals::setGet('ITEM_ID_CRITEO', '');
        } else {
            $tag = "";
        }
        return $tag;

    }

    static function drawPromotionCode($c, $data)
    {
        switch ($c[2]) {
            case 'msg':
                if (!empty(Globals::session("discount_promotion_code"))) {
                    return 'クーポンコードを適用しました';
                } else {
                    if (!empty(Globals::session("error_code_promotion"))) {
                        $error_code_promotion = Globals::session("error_code_promotion");
                        Globals::setSession('error_code_promotion', '');
                        return $error_code_promotion;
                    }
                    return '';
                }
            case 'discount':
                if (!empty(Globals::session("discount_promotion_code"))) {
                    return Globals::session("discount_promotion_code");
                } else {
                    return '0';
                }
            default:
                return 'select field';
        }
    }

    static function drawButtonPromotionCode($c, $data)
    {
        $button = '<input type="text" name="promotion_code" size="40" maxlength="15" style="display: %s" placeholder="クーポンコードを入力" class="text-promotion-code add-promotion-code">
                    <a href="#" style="display: %s" class="btn-promotion-code add-new-item-cart add-promotion-code" onclick="addPromotionCode()" >クーポンコードを利用する</a>
                    <a href="#" style="display: %s" class="btn-promotion-code add-new-item-cart remove-promotion-code" onclick="removePromotionCode()" >クーポンコードを削除</a>';
        $msg = '<dl class="mark info-promotion-code">
								<dt>クーポンコード：</dt>
								<dd>-<span class="discount_promotion">%s</span>円</dd>
				</dl>';
        $discount = self::drawPromotionCode([2 => 'discount'], []);
        if (empty(Globals::session('discount_promotion_code'))) {
            switch ($c[2]) {
                case 'msg':
                    return sprintf($msg, $discount);
                case 'btn':
                    return sprintf($button, '', '', 'none');
                default:
                    return 'select field';
            }
        } else {
            switch ($c[2]) {
                case 'msg':
                    return sprintf($msg, $discount);
                case 'btn':
                    return sprintf($button, 'none', 'none', '');
                default:
                    return 'select field';
            }
        }
    }

    static function drawToolLinkByItem($c, $data)
    {
        global $sql;
        $image_id = '';

        $item = $sql->selectRecord('item', $data['item']);

        if (!empty($item)) {
            $image_id = $item['image_id'];
        }

        return sprintf('%s?design_id=%s', DrawToolConfig::HOST, $image_id);
    }

    static function drawListNameShop($c, $data)
    {
        global $sql;
        $user = Globals::session("LOGIN_ID");
        $shop_id = array();
        $tmp = array();
        $where = $sql->setWhere($c[2], null, $c[3], "=", $user);
        $result = $sql->getSelectResult($c[2], $where);
        $item_where = $sql->setWhere('shop_items', null, 'item_id', "=", Globals::get("id"));
        $item_where = $sql->setWhere('shop_items', $item_where, 'state', "=", "1");
        $item_result = $sql->getSelectResult('shop_items', $item_where);
        while ($rec = $sql->sql_fetch_assoc($item_result)) {
            $shop_id[] = $rec['shop_id'];
        }
        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (in_array($rec['id'], $shop_id)) {
                $tmp[] = '<label><input class="checkbox-owner-shop" type="checkbox" value="' . $rec['id'] . '" data-name="' . $rec['shop_name'] . '" name="shop_item_id[]" checked />' . $rec['shop_name'] . '</label>';
            } else {
                $tmp[] = '<label><input class="checkbox-owner-shop" type="checkbox" value="' . $rec['id'] . '" data-name="' . $rec['shop_name'] . '" name="shop_item_id[]" />' . $rec['shop_name'] . '</label>';
            }
        }
        Globals::setSession("OWNER_SHOP_ID", $shop_id);
        $tmp[] = '<input type="hidden" name="shop_id_CHECK" value="true">';
        return implode("", $tmp);
    }

    static function drawShopInfo($c)
    {

        global $sql;

        $shop_id = Globals::get('shop_id');

        $rawSqlSns = $sql->rawQuery("SELECT * FROM personal_shop_sns WHERE shop_id = '{$shop_id}'");

        while ($sns = $sql->sql_fetch_assoc($rawSqlSns)) {
            $shop[$sns['type'] . '_url'] = $sns['url'];
            $shop[$sns['type'] . '_state'] = $sns['state'];
        }

        if (empty($shop['twitter_url'])) $shop['twitter_url'] = '';
        if (empty($shop['facebook_url'])) $shop['Facebook_url'] = '';
        if (empty($shop['instagram_url'])) $shop['instagram_url'] = '';
        if (empty($shop['tiktok_url'])) $shop['tiktok_url'] = '';
        if (empty($shop['youtube_url'])) $shop['youtube_url'] = '';

        if (empty($shop['twitter_state'])) $shop['twitter_state'] = 0;
        if (empty($shop['facebook_state'])) $shop['facebook_state'] = 0;
        if (empty($shop['instagram_state'])) $shop['instagram_state'] = 0;
        if (empty($shop['tiktok_state'])) $shop['tiktok_state'] = 0;
        if (empty($shop['youtube_state'])) $shop['youtube_state'] = 0;

        if (isset($shop[$c[2]])) {
            return $shop[$c[2]];
        }
    }

    static function drawBreadCrumbItemType($c, $data)
    {
        global $sql;
        if ($sub_category = $sql->keySelectRecord('master_item_web_sub_categories', 'name', Globals::get('sub_category'))) {
            $where = $sql->setWhere('item_web_categories', null, 'sub_category', "=", $sub_category['id']);
        }

        $tmp = array();

        $where = $sql->setWhere('item_web_categories', $where, 'item_type', "=", $c[2]);
        $result = $sql->sql_fetch_assoc($sql->getSelectResult("item_web_categories", $where));

        if (empty($result)) {
            $where = $sql->setWhere('item_web_categories', null, 'is_main', "=", 1);
            $where = $sql->setWhere('item_web_categories', $where, 'item_type', "=", $c[2]);
            $result = $sql->sql_fetch_assoc($sql->getSelectResult("item_web_categories", $where));
        }

        Globals::setGet('item_web_categories', $result);
        $tmp[] = $result['category'];
        $tmp[] = $result['sub_category'];
        $tmp[] = $result['item_type'];
        $frist = $sql->selectRecord('master_item_web_categories', $tmp[0]);
        $second = $sql->selectRecord('master_item_web_sub_categories', $tmp[1]);
        $third = $sql->selectRecord('master_item_type', $tmp[2]);
        Globals::setGet('master_item_web_categories', $frist);
        Globals::setGet('master_item_web_sub_categories', $second);
        Globals::setGet('master_item_type', $third);
        return '<li><a href="/item-detail/category/' . $frist['name'] . '"><span>' . $frist['name'] . '</span></a></li>
            <li><a href="/item-detail/sub-category/' . $second['name'] . '"><span>' . $second['name'] . '</span></a></li>
            <li class="current"><a href="#"><span>' . $third['name'] . '</span></a></li>';
    }

    static function drawMasterItemSub($c, $data)
    {
        global $sql;

        $src = "data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20210%20140%22%3E%3C/svg%3E";
        $html = '<div class="item-rp">
                                        <div class="img">
                                            <img class="lazyload" src=%s data-src=%s alt="thumnail">
                                        </div>
                                        <h3><a>%s</a></h3>
                                    </div>';
        $tmp = '';
        $where = $sql->setWhere('master_item_type_sub', null, 'item_type', "=", $c[2]);
        $where = $sql->setWhere('master_item_type_sub', $where, 'state', "=", 1);
        $result = $sql->getSelectResult("master_item_type_sub", $where);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= sprintf($html, $src, $rec['thumbnail_url'], $rec['name']);
        }
        return $tmp;
    }

    static function drawMasterItemSubPhoneCase($c, $data)
    {
        global $sql;

        $html = '<li><img class="lazyload" data-src="%s" alt="" width="135" height="126"><p>%s</p></li>';
        $tmp = '';
        $where = $sql->setWhere('master_item_type_sub', null, 'item_type', "=", $c[2]);
        $where = $sql->setWhere('master_item_type_sub', $where, 'state', "=", 1);
        $inner_join = $sql->setInnerJoin('master_item_type_sub_sides', 'master_item_type_sub', 'id', 'master_item_type_sub_sides', 'color_id');
        $where = $sql->setWhere('master_item_type_sub_sides', $where, 'is_main', "=", 1);
        $clume = $sql->setClume("master_item_type_sub", null, "name");
        $clume = $sql->setClume("master_item_type_sub_sides", $clume, "preview_url");
        $result = $sql->getSelectResult("master_item_type_sub", $where, null, null, $clume, null, $inner_join);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= sprintf($html, $rec['preview_url'], $rec['name']);
        }
        return $tmp;
    }

    static function urlImageForSeo($c)
    {
        global $sql;
        $tmp = '';
        $where = $sql->setWhere('master_item_type_sub', null, 'item_type', "=", $c[2]);
        $where = $sql->setWhere('master_item_type_sub', $where, 'state', "=", 1);
        $result = $sql->getSelectResult("master_item_type_sub", $where);
        $item_sub = array();
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $item_sub[$rec['name']] = $rec['thumbnail_url'];
            $tmp .= '"' . $rec['thumbnail_url'] . '",';
        }
        Globals::setGet('master_item_type_sub', $item_sub);
        $tmp = substr_replace($tmp, "", -1);
        return $tmp;
    }

    static function urlImageForSeoPhoneCase($c)
    {
        global $sql;
        $tmp = '';
        $where = $sql->setWhere('master_item_type_sub', null, 'item_type', "=", $c[2]);
        $where = $sql->setWhere('master_item_type_sub', $where, 'state', "=", 1);
        $inner_join = $sql->setInnerJoin('master_item_type_sub_sides', 'master_item_type_sub', 'id', 'master_item_type_sub_sides', 'color_id');
        $where = $sql->setWhere('master_item_type_sub_sides', $where, 'is_main', "=", 1);
        $clume = $sql->setClume("master_item_type_sub", null, "name");
        $clume = $sql->setClume("master_item_type_sub_sides", $clume, "preview_url");
        $result = $sql->getSelectResult("master_item_type_sub", $where, null, null, $clume, null, $inner_join);
        $item_sub = array();
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $item_sub[$rec['name']] = $rec['preview_url'];
            $tmp .= '"' . $rec['preview_url'] . '",';
        }
        Globals::setGet('master_item_type_sub', $item_sub);
        $tmp = substr_replace($tmp, "", -1);
        return $tmp;
    }

    static function drawListCatePost($c, $data)
    {
        global $sql;
        $tmp = '';

        $table = "post_new_category";
        $clume = $sql->setClume($table, null, "name");
        $where = $sql->setWhere($table, null, "state", "=", 1);
        $cate_post = $sql->getSelectResult($table, $where, null, null, $clume);

        if ($cate_post->num_rows > 0) {
            $tmp .= '<li class="nav-item arrowed">';

            while ($rec = $sql->sql_fetch_assoc($cate_post)) {
                $tmp .= sprintf('<a href="/category/%s">%s</a>', $rec['name'], $rec['name']);
            }

            $tmp .= "</li>";
        }

        return $tmp;
    }

    static function drawLiMasterItemWeb($c)
    {
        $tmp = '';

        if (!empty(Globals::get('category_data'))) {
            $categories = Globals::get('category_data');
        } else {
            $categories = getLiMasterItemWeb();
        }

        foreach ($categories as $key => $value) {

            $tmp .= '<li class="nav-item arrowed">';
            if (!empty($c[3]) && $c[3] == 'sp') {
                $tmp .= sprintf('<span>%s</span>', $key);
            } else {
                $tmp .= sprintf('<a href="/item-detail/category/%s">%s</a>', $key, $key);
            }

            $tmp .= '<ul class="sub-nav" style="display: none;">';

            if (is_array($value) || is_object($value)) {
                foreach ($value as $key1 => $value1) {

                    $tmp .= '<li class="sub-nav-item next-level">';
                    $tmp .= sprintf('<a href="/item-detail/sub-category/%s">%s</a>', $value1, $value1);

                    $tmp .= "</li>";
                }

            }

            $tmp .= "</ul>";

            $tmp .= "</li>";
        }
        return $tmp;
    }

    static function drawMasterItemSize($c, $data)
    {
        global $sql;
        $tmp = array();

        $where = $sql->setWhere('master_item_type_size', null, 'item_type', "=", $c[2]);
        $where = $sql->setWhere('master_item_type_size', $where, 'state', "=", 1);
        switch ($c[3]) {
            case 'list' :
                $result = $sql->getSelectResult("master_item_type_size", $where);
                while ($rec = $sql->sql_fetch_assoc($result)) {
                    $tmp[] = $rec['name'];
                }
                return implode(",", $tmp);
            case 'count' :
                $result = $sql->getRow("master_item_type_size", $where);
                return $result;
        }
    }

    static function drawDisplayLiMarketOrEvent($c, $data)
    {
        global $sql;
        $tmp = '';

        $clume = $sql->setClume($c[3], null, 'name');
        $clume = $sql->setClume($c[3], $clume, 'preview_image');
        $clume = $sql->setClume($c[3], $clume, 'id');
        $innerJoin = $sql->setInnerJoin($c[2], $c[3], 'parent', $c[2], 'id');
        $where = $sql->setWhere($c[2], null, $c[5], "=", $c[4]);

        $result = $sql->getSelectResult($c[3], $where, null, null, $clume, null, $innerJoin);

        $tmp .= '<ul class="list-items-handled"><li class="nav-item arrowed">';
        if ($c[4] == 'event') {
            $tmp .= '<span>オリジナルプレゼント</span>';
        } else {
            $tmp .= '<span>ブランド一覧</span>';
        }
        $tmp .= '<ul class="sub-nav" style="display: none;">';
        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($c[4] == 'event') {
                $tmp .= sprintf('<li class="sub-nav-item"><a href="/item-detail/sub-category/%s&design=event">%s</a></li>', $rec['name'], $rec['name']);
            } else {
                $tmp .= sprintf('<li class="sub-nav-item"><a href="/item-detail/sub-category/%s&design=maker">%s</a></li>', $rec['name'], $rec['name']);
            }

        }
        $tmp .= '</ul></li></ul>';

        return $tmp;

    }

//    static function checkNavItemActiveSideBar()
//    {
//        global $sql;
//        $tmp = array();
//        $type = Globals::get('type');
//        if ($type == 'master_item_web_sub_categories' || $type == 'master_item_web_categories') {
//            if(empty(Globals::get("name"))){
//                $id = '';
//            } else {
//                if (!$item = $sql->keySelectRecord($type,'name', Globals::get("name"))) {
//                    SystemUtil::errorPage();
//                }
//                $id = $item['id'];
//            }
//        } else {
//            $id = Globals::get('id');
//        }
//
//        switch ($type){
//            case 'master_item_web_categories':
//               $tmp['item_type_frist'] = $id;
//               $tmp['item_type_second'] = '';
//               $tmp['item_type_third'] = '';
//               break;
//            case 'master_item_web_sub_categories':
//                $tmp['item_type_second'] = $id;
//                if ($rec_temp = $sql->selectRecord("master_item_web_sub_categories", $id)) {
//                    $tmp['item_type_frist'] = $rec_temp['parent'];
//                }
//                $tmp['item_type_third'] = '';
//                break;
//        }
//
//        return $tmp;
//    }

    static function DrawPartsSideBarItemTypeCategories($c, $data)
    {
        global $cc;
        $result = '';

        $design = Globals::get('design');

        $sidebars = [
            $c[2] => $c[2]
        ];

        $sidebars = array_merge([$design => $design], $sidebars);

        foreach ($sidebars as $sidebar) {
            $result .= $cc->run(SystemUtil::getPartsTemplate('other', 'side_bar_parts', $sidebar));
        }

        return $result;
    }

    static function DrawSelectMasterItemType($c, $data)
    {
        global $sql;
        $table = 'master_item_type';

        $tmp = '';
        $tmp .= '<select name="item_type">';

        $where = '';
        if ($c[2] == 'master_item_type_page' || $c[2] == 'item_assets') {
            $where = $sql->setWhere($table, $where, "id", "NOT IN", "(SELECT item_type FROM " . $c[2] . " WHERE item_type IS NOT NULL )");
        }

        $result = $sql->getSelectResult($table, $where);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= sprintf('<option value="%s">%s</option>', $rec['id'], $rec['name']);
        };
        $tmp .= '</select>';

        return $tmp;
    }

    static function drawScriptBreadCrumbsList($c, $data)
    {
        global $sql;
        $list_item = array();
        $web_categories = '';
        $sub_categories = '';
        $item_type_name = '';
        $item_type_id = '';
        $markets = [
            'market' => 'ネット販売',
            'market_pickup' => 'ネット販売 ピックアップ一覧',
            'market_rankings' => 'ネット販売 ランキング一覧',
            'market_collaboration' => 'ネット販売 コラボ一覧',
        ];

        $market_pages = array_keys($markets);

        if (!empty($c[2]) && !empty($c[3])) {
            if ($c[2] == 'master_item_web_sub_categories') {
                if (empty(Globals::get('master_item_web_sub_categories'))) {
                    $rec = Globals::get('master_item_web_categories');
                } else {
                    $rec = Globals::get('master_item_web_sub_categories');
                }

                if (!empty($c[3])) {
                    return $rec[$c[3]];
                }
            }
        }

        if (Globals::get('type') == 'master_item_type') {
            $web_categories = Globals::get('master_item_web_categories')['name'];
            $sub_categories = Globals::get('master_item_web_sub_categories')['name'];
            $item_type_name = Globals::get('master_item_type')['name'];
            $item_type_id = Globals::get('id');
        } elseif (Globals::get('type') == 'master_item_web_categories') {
            $web_categories = Globals::get('name');
        } elseif (Globals::get('type') == 'master_item_web_sub_categories') {
            $sub_categories = Globals::get('name');
            $rec_sub_categories = $sql->keySelectRecord('master_item_web_sub_categories', "name", $sub_categories);
            $rec_web_categories = $sql->selectRecord('master_item_web_categories', $rec_sub_categories['parent']);
            $web_categories = $rec_web_categories['name'];
        }

        if (!empty($web_categories)) {

            $master_item_web_categories = sprintf('{
                                                    "@type": "ListItem",
                                                    "position": 2,
                                                      "item":
                                                      {
                                                        "@id": "/item-detail/category/%s",
                                                        "name": "%s" 
                                                      }
                                                }', $web_categories, $web_categories);
            array_push($list_item, $master_item_web_categories);
        }

        if (!empty($sub_categories)) {

            $master_item_web_sub_categories = sprintf('{
                                                    "@type": "ListItem",
                                                    "position": 3,
                                                      "item":
                                                      {
                                                        "@id": "/item-detail/sub-category/%s",
                                                        "name": "%s" 
                                                      }
                                                }', $sub_categories, $sub_categories);
            array_push($list_item, $master_item_web_sub_categories);
        }

        if (!empty($item_type_name) && !empty($item_type_id)) {

            $master_item_web_sub_categories = sprintf('{
                                                    "@type": "ListItem",
                                                    "position": 4,
                                                      "item":
                                                      {
                                                        "@id": "%s",
                                                        "name": "%s" 
                                                      }
                                                }', ccDraw::itemCategories([2 => $item_type_id], $master_item_web_sub_categories), $item_type_name);
            array_push($list_item, $master_item_web_sub_categories);
        }

        $page = Globals::get('p');
        if (in_array($page, $market_pages)) {
            $market = self::getBreadCrumb($markets['market'], '/market');
            array_push($list_item, $market);
            unset($market_pages[0]);

            if (in_array($page, $market_pages)) {
                $another_market = self::getBreadCrumb($markets[$page], sprintf('/%s', str_replace('_', '-', $page)), 3);
                array_push($list_item, $another_market);
            }
        }

        if (!empty($c[2]) && $c[2] == 'item_detail') {
            $item_detail = self::getBreadCrumb('商品と価格一覧', '/item-detail');
            array_push($list_item, $item_detail);
        }

        if (Globals::get('type') == 'post_new') {
            $title = 'オリジナルプリントTシャツ コラム (一覧)';
            if (!empty($data['id']) && !empty($data['title'])) {
                $title = $data['title'];
            }

            $position = 2;
            $post_new_category = $sql->selectRecord('post_new_category', $data['post_category_id']);

            if (!empty($post_new_category)) {
                $news = self::getBreadCrumb(sprintf('%sコラム (一覧)', $post_new_category['name']), sprintf('/category/%s', $post_new_category['name']), $position);
                array_push($list_item, $news);
                $position++;
            }

            $news = self::getBreadCrumb($title, '', $position);
            array_push($list_item, $news);
        } elseif (Globals::get('type') == 'item') {
            if (!empty($data['id']) && !empty($data['name'])) {
                $item = self::getBreadCrumb($markets['market'], '/market');
                array_push($list_item, $item);

                $position = 3;
                $category = self::drawNormalCatC1(null, $data);
                $categories = explode('</a>', $category);

                foreach ($categories as $category) {
                    preg_match('/href=[\'"](.*)[\'"]/im', $category, $matches);

                    if (count($matches) == 2) {
                        $item = self::getBreadCrumb(strip_tags($category), sprintf('/%s', ltrim($matches[1], '/')), $position);
                        array_push($list_item, $item);
                        $position++;
                    }
                }

                $item = self::getBreadCrumb($data['name'], '', $position);
                array_push($list_item, $item);
            }
        }

        $script = '';

        $script .= '<script type="application/ld+json">';

        $script .= sprintf('{
                        "@context": "http://schema.org",
                        "@type": "BreadcrumbList",
                        "itemListElement":
                        [
                          {
                            "@type": "ListItem",
                            "position": 1,
                            "item":
                            {
                                "@id": "/",
                                "name": "Ondemand" 
                            }
                          },%s
                        ]', implode(",", $list_item));

        $script .= '}</script>';

        return $script;
    }

    static function drawListMasterItemWebCategories()
    {
        global $sql;
        global $cc;

        $table = 'master_item_web_categories';

        $where = $sql->setWhere($table, null, "state", "=", 1);
        $result = $sql->getSelectResult($table, $where);

        $template = SystemUtil::getPartsTemplate($table, 'list');

        $tmp = "";
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= $cc->run($template, $rec);
        }
        return $tmp;

    }

    static function drawContent($c, $data)
    {
        return $data[$c[2]];
    }

    static function drawSelectedPay($c, $data, &$draw)
    {
        if (!empty(Globals::post('pay_type'))) {
            $error = sprintf("$('[value=\"%s\"]').prop('checked', true)", Globals::post('pay_type'));
        } else {
            $error = '';
        }

        return $error;
    }

    static function drawAmazonCheckoutSessionId($c, $data)
    {
        $param = "";
        $amazonCheckoutSessionId = Globals::session('amazonCheckoutSessionId');
        if (!empty($amazonCheckoutSessionId)) {
            $param .= sprintf('&amazonCheckoutSessionId=%s', $amazonCheckoutSessionId);
        }

        return $param;
    }

    static function drawSelectWishList($c, $data)
    {
        global $sql;

        $tmp = '';
        $wishlist = findItemWishList($c[2]);

        $table = 'wish_list';

        $where = $sql->setWhere($table, null, 'user', '=', Globals::session('LOGIN_ID'));
        $order = $order = $sql->setOrder($table, null, "regist_unix", "DESC");

        $result = $sql->getSelectResult($table, $where, $order);
        $tmp .= sprintf('<div class="div_select"><select name="name" data-id="%s" class="list_name_wish list_name_wish_sub">', $wishlist['item']);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($rec['status'] != 0) {
                $display = '公開';
            } else {
                $display = '非公開';
            }
            if (!empty($wishlist['wish_list']) && $wishlist['wish_list'] == $rec['id']) {
                $tmp .= sprintf('<option value="%s" selected="selected">%s    %s</option>', $rec['id'], $rec['name'], $display);
            } else {
                $tmp .= sprintf('<option value="%s">%s      %s</option>', $rec['id'], $rec['name'], $display);
            }
        }
        $tmp .= '</select><div class="icon_select"></div></div>';

        return $tmp;
    }

    static function drawSelectWishListDefault($c, $data)
    {
        global $sql;

        $tmp = '';
        $table = 'wish_list';

        $where = $sql->setWhere($table, null, 'user', '=', Globals::session('LOGIN_ID'));
        $order = $order = $sql->setOrder($table, null, "regist_unix", "ASC");

        $result = $sql->getSelectResult($table, $where, $order);
        if ($c[2] == 'pc') {
            $tmp .= '<div class="div_select"><select name="name" class="list_name_wish select_default">';
        } else {
            $tmp .= '<div class="div_select"><select name="name" class="list_name_wish list_name_wish2 select_default">';
        }

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($rec['status'] != 0) {
                $display = '公開';
            } else {
                $display = '非公開';
            }
            $tmp .= sprintf('<option value="%s">%s      %s</option>', $rec['id'], $rec['name'], $display);
        }
        $tmp .= '</select><div class="icon_select"></div></div>';

        return $tmp;
    }

    static function drawListSubCat($c, $data)
    {
        global $sql;
        $tmp = '';

        if (!empty(Globals::get('category_data'))) {
            $data_rec = Globals::get('category_data');

        } else {
            $data_rec = array();
            $clume = $sql->setClume('master_item_web_categories', null, 'name', null, 'master_item_web_categories');
            $clume = $sql->setClume('master_item_web_sub_categories', $clume, 'name', null, 'master_item_web_sub_categories');
            $inner_join = $sql->setInnerJoin('master_item_web_sub_categories', 'master_item_web_categories', 'id', 'master_item_web_sub_categories', 'parent');
            $where = $sql->setWhere('master_item_web_categories', null, "state", "=", 1);
            $where = $sql->setWhere('master_item_web_sub_categories', $where, "state", "=", 0);

            $result = $sql->getSelectResult('master_item_web_categories', $where, null, null, $clume, null, $inner_join);

            while ($rec = $sql->sql_fetch_assoc($result)) {
                $data_rec[$rec['master_item_web_categories']][] = $rec['master_item_web_sub_categories'];
            }

        }
        $num = ceil(count($data_rec) / 2);

        if (!empty($c[2]) && $c[2] == 'sp') {
            foreach ($data_rec as $key => $value) {
                $tmp .= sprintf('<ul class="sub-nav_ft" style="display: none;"><li class="nav-itemft arrowed"><span>%s</span>', $key);
                $tmp .= '<ul class="accordion-footer-item sub-navft2" style="display: none;">';

                if (is_array($value) || is_object($value)) {
                    foreach ($value as $sub_key => $sub_value) {
                        $tmp .= sprintf('<li class="accordion-footer-item sub-nav-itemft next-level"><a href="/info.php?type=master_item_web_sub_categories&amp;name=%s">%s</a></li>', $sub_value, $sub_value);
                    }
                }

                $tmp .= '</ul></li></ul>';
            }
        } else {
            $i = 0;
            $tmp .= "<div class=\"ft_list_item_left\">";
            foreach ($data_rec as $key => $value) {
                $tmp .= sprintf('<div class="cat_1_ft">%s</div>', $key);
                $tmp .= '<ul class="cat_2_ft">';

                if (is_array($value) || is_object($value)) {
                    foreach ($value as $sub_key => $sub_value) {
                        $tmp .= sprintf('<li><a href="/info.php?type=master_item_web_sub_categories&amp;name=%s">%s</a></li>', $sub_value, $sub_value);
                    }
                }

                $tmp .= "</ul>";
                $i++;
                if ($i == $num) {
                    $tmp .= "</div><div class=\"ft_list_item_right\">";
                }
            }
            $tmp .= '</div>';
        }

        return $tmp;
    }

    static function drawDisplayItemType($c)
    {
        global $cc, $sql;
        $ranks = ['no_1', 'no_2', 'no_3'];
        $itemRank = [];
        $i = 1;

        $data = getItemType();

        if ($c[2] == '16') {
            $tmp = '<ul class="product-page-list-t product-page-list-t-ct product-item-details-list">';
        }
        if ($c[2] == '8') {
            $tmp = '<ul class="product-page-list-t product-page-list-t-ct product-item-details-list-sp">';
        }

        $template = SystemUtil::getPartsTemplate('master_item_type', 'list');

        if (empty($data)) {
            $tmp .= '該当する商品を見つけません。';

            return $tmp;
        } else {
            $items = $sql->rawQuery('SELECT * FROM item_ranking');
            while ($rec = $sql->sql_fetch_assoc($items)) {
                foreach ($ranks as $rank) {
                    $itemRank[] = $rec[$rank];
                }
            }
            foreach ($data as $value1) {
                if (in_array($value1['id'], $itemRank)){
                    $value1['ranking'] = 1;
                }else {
                    $value1['ranking'] = 0;
                }
                if (!empty($value1['print_method_id'])) {
                    $result = $sql->selectRecord('print_method', $value1['print_method_id']);
                    if ($result['state'] == 1) {
                        $value1['print_method'] = $result['title'];
                    }
                }
                $tmp .= $cc->run($template, $value1);
                if ($i == $c[2]) {
                    break;
                }
                $i++;
            }
            if ($i < count($data)) {
                $tmp .= '</ul>
            <div style="text-align: center"><img src="common/img/load.gif" class="loading" style="display: none"></div>    
			<div class="btn-load-more btn-black-radius d-flex justify-content-center dpt-50">
				<a class="more-content">もっと見る</a>
			</div>';
            } else {
                $tmp .= '</ul>
            <div style="text-align: center"><img src="common/img/load.gif" class="loading" style="display: none"></div>    
			<div class="btn-load-more btn-black-radius d-flex justify-content-center dpt-50">
				<a class="more-content" style="display: none">もっと見る</a>
			</div>';
            }
        }

        return $tmp;
    }

    static function drawButtonCategoryPageItemDetail()
    {
//        $tmp = '<div class="category_box category_box_pc">';
        $tmp = '';
        $categories = getLiMasterItemWeb();
//        $icon = Globals::get('icon');

//        foreach ($categories as $key => $value) {
//            $tmp .= sprintf('<div class="wrapper_list_category_pc"><h3>%s</h3><ul>', $key);
//            foreach ($value as $sub_key => $sub_value) {
//                $img_icon = '';
//                if (!empty($icon[$sub_key])) {
//                    $img_icon = sprintf('<div class="ct_img_zise"><img class="lazyload" data-src="%s" alt="%s"></div>',$icon[$sub_key],$sub_value);
//                }
//                $tmp .= sprintf('<li><a class="js-anchor-link" href="#sub_category_%s">%s%s</a></li>', $sub_key, $img_icon, $sub_value);
//            }
//            $tmp .= '</ul></div>';
//        }
//        $tmp .= '</div>';
        return $tmp;
    }

    static function displayPreviewSide($c, $data)
    {
        global $cc;

        $sides = getPreviewSide($c[2]);
        $template = SystemUtil::getPartsTemplate('price_report', 'preview_side');

        if (!empty(Globals::session('ITEM_PRICE'))) {
            $session = Globals::session('ITEM_PRICE');
        }

        $tmp = "";
        foreach ($sides as $key => $value) {

            if (isset($session[$c[3]]['side'][$key])) {
                $value['checked'] = 1;
            } else {
                $value['checked'] = 0;
            }

            $tmp .= $cc->run($template, $value);
        }

        return $tmp;
    }

    static function displayItemsReportPrice($c, $data)
    {
        global $cc;

        if (!empty(Globals::session('ITEM_PRICE'))) {

            $session = Globals::session('ITEM_PRICE');
            $template = SystemUtil::getPartsTemplate("price_report", 'list');
            $tmp = '';

            foreach ($session as $key => $value) {
                $value['id'] = $key;
                $tmp .= $cc->run($template, $value);
            }
            $tools = array_keys(Globals::session('ITEM_PRICE'));
            $url_tool = Extension::getDrawToolLinkString(null, null, null, $tools[0]);
            $cc->setVariable('url_tool', $url_tool);
            return $tmp;

        } else {
            $url_tool = DrawToolConfig::HOST;
            $cc->setVariable('url_tool', $url_tool);
            $cc->setVariable('hidden', 'true');
            return '<p class="pr_noti">アイテムが選択されていません。</p>';
        }
    }

    static function displayPriceReport($c, $data)
    {
        global $cc;
        $tmp = '';

        if (!empty(Globals::session('PRICE_REPORT'))) {

            $price_report = Globals::session('PRICE_REPORT');
        } else {

            $price_report = array("next" => 10, "discount" => 0, "discount_par" => 0, "next_discount_par" => 2, "price_total" => 0, "total" => 0, "price_discount" => 0);
        }

        $template = SystemUtil::getPartsTemplate("price_report", 'price');

        $tmp .= $cc->run($template, $price_report);

        return $tmp;

    }

    static function drawSelectCategoriesVoices($c, $data)
    {
        global $sql;

        $where = null;
        $item_type = Globals::get('item_type');
        $categories = Globals::get('categories');
        $sub_categories = Globals::get('sub_categories');
        $tmp = '<div class="col_select_lr">';

        if (!empty($item_type) && empty($categories) && empty($sub_categories)) {
            $where_item = $sql->setWhere('item_web_categories', null, 'item_type', '=', $item_type);
            $where_item = $sql->setWhere('item_web_categories', $where_item, 'is_main', '=', 1);
            $item = $sql->sql_fetch_assoc($sql->getSelectResult('item_web_categories', $where_item));

            Globals::setGet('categories', $item['category']);
            Globals::setGet('sub_categories', $item['sub_category']);
            $categories = $item['category'];
            $sub_categories = $item['sub_category'];
        }

        if ($c[2] == 'master_item_web_categories') {
            $tmp .= '<p><strong>商品カテゴリ</strong></p>';
            $where = $sql->setWhere($c[2], $where, 'state', '=', 1);
            $select_default = '<option value="">全て</option>';
        } elseif ($c[2] == 'master_item_type') {
            $tmp .= '<p><strong>アイテム</strong></p>';
            if (!empty($categories) && empty($sub_categories)) {
                $where = $sql->setWhere($c[2], $where, 'id', 'IN', sprintf('SELECT item_type FROM item_web_categories WHERE item_web_categories.category = %s', $categories));
            }
            if (!empty($categories) && !empty($sub_categories)) {
                $where = $sql->setWhere($c[2], $where, 'id', 'IN', sprintf('SELECT item_type FROM item_web_categories WHERE item_web_categories.category = %s AND item_web_categories.sub_category = %s', $categories, $sub_categories));
            }
            $where = $sql->setWhere($c[2], $where, 'state', '=', 1);
            $select_default = '<option value="">選択してください</option>';
        } else {
            if (empty($categories)) {
                return '';
            } else {
                $where = $sql->setWhere($c[2], $where, 'parent', '=', $categories);
            }
            $tmp .= '<p><strong>商品サブカテゴリ別</strong></p>';
            $where = $sql->setWhere($c[2], $where, 'state', '=', 0);
            $select_default = '<option value="">全て</option>';
        }

        $tmp .= '<div class="select_box_lr">';
        $tmp .= sprintf('<select name="%s" id="%s">', $c[3], $c[3]);
        $tmp .= $select_default;

        $order = $sql->setOrder($c[2], null, "id", "ASC");

        $result = $sql->getSelectResult($c[2], $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (Globals::get($c[3]) == $rec['id']) {
                $tmp .= sprintf('<option value="%s" selected>%s</option>', $rec['id'], $rec['name']);
            } else {
                $tmp .= sprintf('<option value="%s">%s</option>', $rec['id'], $rec['name']);
            }
        }
        $tmp .= '</select></div></div>';

        return $tmp;
    }

    static function drawStarPicked($c, $data)
    {
        $tmp = '<ul id=\'stars\'>';
        for ($i = 0; $i < 5; $i++) {
            if ($c[2] > $i) {
                $tmp .= sprintf('<li class=\'star selected\' data-value=\'%s\'>', $i);

            } else {
                $tmp .= sprintf('<li class=\'star\' data-value=\'%s\'>', $i);
            }
            $tmp .= '<i class=\'fa fa-star fa-fw\'></i>';
            $tmp .= '</li>';
        }
        $tmp .= '</ul>';

        return $tmp;

    }

    static function drawTagReview($c, $data)
    {
        global $sql;
        global $cc;
        $tmp = '';

        $where = $sql->setWhere($c[4], null, 'public', '=', 1);
        if (!empty($c[5]) && $c[5] != 'review_top') {
            $where = $sql->setWhere($c[4], $where, $c[2], '=', $c[3]);
        }

        $order = $sql->setOrder($c[4], null, "regist_unix", "DESC");

        $template = SystemUtil::getPartsTemplate('voices', $c[5]);

        $result = $sql->getSelectResult($c[4], $where, $order, array(0, 6));

        if ($result->num_rows == 0) {
            $where_other = $sql->setWhere($c[4], null, 'public', '=', 1);
            $order_other = $sql->setOrder($c[4], null, "RAND", time());

            $result_other = $sql->getSelectResult($c[4], $where_other, $order_other, array(0, 6));

            while ($rec = $sql->sql_fetch_assoc($result_other)) {
                $tmp .= $cc->run($template, $rec);
            }

            return $tmp;
        } else {
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $tmp .= $cc->run($template, $rec);
            }

            return $tmp;
        }

    }

    static function drawHiddenPageRedirect()
    {
        $page = Globals::get('page_redirect');
        $item = Globals::get('item');

        if (empty($page) && empty($item)) {

            return '';
        } else {

            return sprintf('<input type="hidden" name="page_redirect" value="%s"><input type="hidden" name="item" value="%s">', $page, $item);
        }
    }

    static function drawSelectColorSizeItemBlank($c)
    {
        global $sql;

        $clume = $sql->setClume($c[2], null, 'item_code');
        $clume = $sql->setClume($c[2], $clume, 'name');
        $clume = $sql->setClume($c[2], $clume, 'item_type');

        $where = $sql->setWhere($c[2], null, 'state', '=', 1);

        $group = $sql->setGroup($c[2], null, 'item_code');
        $order = $sql->setOrder($c[2], null, "item_code", "DESC");
        $result = $sql->getSelectResult($c[2], $where, $order, null, $clume, $group);
        $sizes = [];
        $tmp = '';
        $tmp .= '<option value="">全て</option>';

        if (!empty($c[3]) && $c[3] == 'color') {
            while ($rec = $sql->sql_fetch_assoc($result)) {
                if (!empty(Globals::get('ITEM_BLANK')) && !in_array($rec['item_type'], Globals::get('ITEM_BLANK'))) {
                    continue;
                }

                $tmp .= sprintf('<option value="%s">%s</option>', $rec['item_code'], mb_convert_kana($rec['name'], "KVr"));
            }
        } else {
            while ($rec = $sql->sql_fetch_assoc($result)) {
                if (!empty(Globals::get('ITEM_BLANK')) && !in_array($rec['item_type'], Globals::get('ITEM_BLANK'))) {
                    continue;
                }

                $sizes[$rec['name']] = $rec['name'];
            }

            if (!empty($sizes)) {
                sort($sizes);

                foreach ($sizes as $size) {
                    $tmp .= sprintf('<option value="%s">%s</option>', $size, mb_convert_kana($size, "KVr"));
                }
            }
        }
        return $tmp;
    }

    static function drawApplePayInfo($c, $rec)
    {
        $info = '';
        if (!empty(Globals::session('discount_promotion_code'))) {
            $discount_promotion_code = Globals::session('discount_promotion_code');
        } else {
            $discount_promotion_code = 0;
        }

        $discount_rank = Extension::discountPrice([2 => 'discount_rank']);
        $discount_data = getCartDiscount();
        $gift_total = getCartGiftTotale();
        $total = $gift_total + getCartPrice() - $discount_data["discount"] - $discount_promotion_code;
        $total_without_promotion = $total + $discount_promotion_code;
        $cost_tmp = getCartPrice() + $discount_data["discount"] + $gift_total - $discount_promotion_code - $discount_rank;
        $tax = ceil(($cost_tmp - (int)$rec["pay_point"]) * getTaxRate()) + getPostage($rec["add_pre"],
                $total_without_promotion, $rec["pay_type"]);
        $cost = $cost_tmp - $rec["pay_point"];

        $data = [
            'debug' => DEBUG,
            'cost' => $cost + $tax,
            'PRODUCTION_MERCHANTIDENTIFIER' => PRODUCTION_MERCHANTIDENTIFIER,
            'PRODUCTION_COUNTRYCODE' => PRODUCTION_COUNTRYCODE,
            'PRODUCTION_CURRENCYCODE' => PRODUCTION_CURRENCYCODE,
            'PRODUCTION_DISPLAYNAME' => PRODUCTION_DISPLAYNAME,
        ];

        foreach ($data as $key => $value) {
            $info .= sprintf('<span id="%s">%s</span>', $key, $value);
        }

        return $info;
    }

    static function setJsFile($c)
    {
        Globals::setJsFile($c['2']);
    }

    static function drawJsFiles()
    {
        $script = '';

        foreach (Globals::getJsFiles() as $file) {
            $script = sprintf('%s<script type="text/javascript" src="%s"></script>', $script, $file);
        }

        return $script;
    }

    static function drawScript($c)
    {
        global $device_path;
        $type = 'base';
        $script = '';

        if ($device_path != 'pc/') {
            $device_path = 'sp/';
        }

        if (is_detail_page()) {
            $type = 'detail_page';
        } elseif ($_SERVER['SCRIPT_NAME'] == '/index.php' || !empty($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] === '/market' && $device_path === 'sp/') {
            $type = 'home';
        } else {
            if ($device_path == 'sp/') {
                $script .= '<script type="text/javascript" src="/common/design/user/js/slick/slick.min.js"></script>';
            }

            if (Extension::isBattle() === 0) {
                $script .= '<script type="text/javascript" src="/common/js/libs/bootstrap.min.js"></script>';
            }

            if (!empty($c[3]) && $c[3] === 'item') {
                $type = $c[3];
            }
        }

        return sprintf('%s<script type="text/javascript" src="/common/js/%snobody_%s.min.js%s"></script>%s', $script, $device_path, $type, $c[2], self::drawJsFiles());
    }

    static function drawSwiperScript($c)
    {
        global $device_path;

        $script = '';

        if (Globals::get('p') !== 'cart' && strpos($_SERVER['REQUEST_URI'], 'page.php?p=') !== false ||
            $device_path === 'smart/' && is_detail_page()) {
            $script = '<script type="text/javascript" src="/common/lib/swiper.min.js"></script>';
        }

        if (!is_detail_page()) {
            $script .= '<script type="text/javascript" src="/common/design/user/js/slick/slick.min.js"></script>';
        }

        return $script;
    }

    static function drawStyle($c)
    {
        if (is_detail_page()) {
            $file = '/common/css/sp/details-page-new-sp.css';
        } elseif ($_SERVER['SCRIPT_NAME'] == '/index.php') {
            $file = '/common/css/sp/nobody_home.min.css';
        } else {
            $file = '/common/css/sp/nobody_base.min.css';
        }

        return sprintf('<link rel="stylesheet" href="%s%s">', $file, $c[2]);
    }

    static function drawTopImage()
    {
        if (empty(Globals::session('fist_display_top_image'))) {
            Globals::setSession('fist_display_top_image', true);
            $image = '<img id="img-top" class="display-none lazyload" data-src="/common/img/sp/img-top_visual_2.png?v=1.2" alt="1枚からでも オリジナルTシャツが作れます"><div id="lazyloading" class="lazyloading display-none"></div>';
        } else {
            $image = '<img src="/common/img/sp/img-top_visual_2.png?v=1.2" alt="1枚からでも オリジナルTシャツが作れます">';
        }

        return $image;
    }

    static function drawStarReviewItem($c)
    {
        global $sql;

        $table = 'voices';
        if (!empty($c['compare']) && $c['compare'] == true) {
            $where = $sql->setWhere($table, null, 'item_type', '=', $c['id']);
            $where = $sql->setWhere($table, $where, 'public', '=', 1);
        } else {
            $where = $sql->setWhere($table, null, $c[2], '=', $c[3]);
            $where = $sql->setWhere($table, $where, 'public', '=', 1);
        }
        $review = $sql->getRow($table, $where);

        if (!empty($c[4]) && $c[4] == 'count') {
            return RandomStar::random_star($c[3], $review);
        }
        $total_star = $sql->getSum($table, "star", $where);

        if ($review == 0) {
            $star = 5;
        } else {
            $star = floor(($total_star / $review) * 2) / 2;
        }
        $tmp = '<div class="box-rating-star"><div class="item-rating">';

        $count = 1;
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= floor($star)) {
                $tmp .= '<i class="fa fa-star" aria-hidden="true"></i>';
            } else {
                if (strpos($star, '.') !== false && $count == 1) {
                    $tmp .= '<i class="fa fa-star-half-o" aria-hidden="true"></i>';
                    $count++;
                } else {
                    $tmp .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                }
            }
        }

        $tmp .= sprintf('</div><div class="text-item-rating">%s</div></div>', RandomStar::random_star($c[3], $review));

        return $tmp;

    }

    static function drawBreadCrumb()
    {
        global $sql;
        $tmp = '<li class="home"><a href="/">オリジナルTシャツ作成のUp-T(TOP)</a></li><li><a href="/search.php?type=voices">オリジナルプリントTシャツ作成事例（レビュー/口コミ/評判）</a></li>';

        if (!empty(Globals::get('categories'))) {
            $categories = $sql->selectRecord('master_item_web_categories', Globals::get('categories'));
            $tmp .= sprintf('<li><a href="/item-detail/category/%s">%s作成事例（レビュー/口コミ/評判）</a></li>', $categories['name'], $categories['name']);
        }

        if (!empty(Globals::get('sub_categories'))) {
            $sub_categories = $sql->selectRecord('master_item_web_sub_categories', Globals::get('sub_categories'));
            $tmp .= sprintf('<li><a href="/item-detail/sub-category/%s">%s作成事例（レビュー/口コミ/評判）</a></li>', $sub_categories['name'], $sub_categories['name']);
        }

        if (!empty(Globals::get('item_type'))) {
            $item_type = $sql->selectRecord('master_item_type', Globals::get('item_type'));
            $tmp .= sprintf('<li>%s作成事例（レビュー/口コミ/評判）</li>', $item_type['name']);
        }

        return $tmp;
    }

    static function drawInputHidden()
    {
        return sprintf('<input type="hidden" name="user" value="%s">', Globals::get('user'));
    }


    static function drawPersonalShopInfoNotification($c)
    {
        global $sql;

        $table = "personal_shop_info_notification";
        $where = $sql->setWhere($table, null, "state", "=", 1);
        $where = $sql->setWhere($table, $where, "date_time", "=", date("Y-m-d"));
        $order = $sql->setOrder($table, null, "date_time", "DESC");

        if ($data = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, $order, array(0, 1)))) {
            return $data['content'];
        } else {
            return '';
        }
    }

    static function drawListUserShop()
    {
        $list_user_shop = getListShop();
        $content = '';
        foreach ($list_user_shop as $shop_rec) {
            $shop_id = $shop_rec['id'];
            $url = sprintf('%s?is_admin=true&session=%s&m_type=%s&edit_shop=%s', $list_user_shop[0]['url'], Globals::session('DRAW_TOOL_SESSION'), 'item', $shop_id);
            $content .= "<button type=\"button\" class=\"c-submitBtn c-submitBtn--full\" style='margin-bottom: 10px' onclick=\"window.location.href='{$url}'\">
                {$shop_rec['shop_name']}
            </button>";
        }

        return $content;
    }

    /**
     * Draw edit shop button and load more report button
     *
     * @param $c
     *
     * @param $data
     *
     * @return string
     */
    static function originalShop($c, $data)
    {
        $tmp = '';
        global $sql;

        // get current user
        $table = 'personal_shop_info';

        $where = $sql->setWhere($table, null, 'user', "=", Globals::session("LOGIN_ID"));
        $result = $sql->getSelectResult($table, $where);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (empty($c[2])) {
                $tmp .= '<a href="' . $rec['url'] . '" target="_blank">
                            <button type="button" class="c-submitBtn c-submitBtn--full" position="right" style="margin: 10px 0px; font-size: 12px">
                                <div class="c-submitBtn__icon"><!---->
                                    <div>' . $rec['shop_name'] . 'ショップを見る</div>
                                </div>
                            </button>
                        </a>';
            } else {
                $tmp .= ' <li class="item_2ovouckr">
                                    <a href="' . $rec['url'] . '" class="link_30Wo7jTZ"><!---->
                                        <i class="i-shop icon_151YJmHz"></i>
                                        <span class="title_30kLmzEb">' . $rec['shop_name'] . 'ショップを見る</span>
                                        <i data-badge="0" class="badge_3mvQiXKB"></i>
                                        <i class="i-angleRight iconRight_14vkXTL5"></i>
                                    </a>
                                </li>';
            }
        }
        return $tmp;
    }


    static function drawListPayStore2($c)
    {
        global $sql;

        $user = Globals::session('LOGIN_ID');

        $today = date_create(date("Y-m-d"));
        $date = date_modify($today, "-1 months");
        $date_y = date_format($date, "Y");
        $date_m = date_format($date, "n");

        $string = "<span style='font-size:25px;display:block;margin:20px'>" . $date_y . "年" . $date_m . "月</span>";

        $tmp = array();
        $tmp["date_y"] = $date_y;
        $tmp["date_m"] = $date_m;

        // get StatisticPay2 from time
        $result = getStatisticPay2($user, $tmp);
        $tmp["fee_total"] = $result["fee_user"] + $result["fee_owner"] + $result["fee_option"];

        $string .= "<span class='note'>" . $tmp["fee_total"] . "円</span>";

        return $string;
    }

    static function drawButtonLoadMore($c)
    {
        $tmp = '';

        if (!empty($c[2]) && $c[2] == "statisticPay2") {
            $tmp .= '<div class="load-more">
                        <a href="/edit.php?type=user">
                            <button type="button" class="c-submitBtn c-submitBtn--small c-submitBtn--full" style="border: 2px solid gray; color: gray; background: white">
                                <div class="c-submitBtn__icon">
                                    <div>
                                        販売用プロフィール設定
                                    </div>
                                </div>
                            </button>
                        </a>
                    </div>';
            $tmp .= '<div class="load-more">
                        <button type="button" class="c-submitBtn c-submitBtn--small c-submitBtn--full" onclick="window.location.href=\'/' . $c[2] . '.php?type=pay\'">
                            <div class="c-submitBtn__icon">
                                <div>
                                    売上レポート
                                </div>
                            </div>
                        </button>
                    </div>';
        }
        return $tmp;
    }

    /**
     * Draw original shop item
     *
     * @param $c
     * @param $data
     *
     * @return string
     */
    static function originalShopItem($c, $data)
    {
        $tmp = '';

        $list_shop = getListShop();

        foreach ($list_shop as $row) {
            $url = sprintf('%s?is_admin=true&session=%s&m_type=%s&shop_id=%s', $list_shop[0]['url'], Globals::session('DRAW_TOOL_SESSION'), 'shop_items', $row['id']);
            $tmp .= '<a href="' . $url . '">
                            <button type="button" class="c-submitBtn c-submitBtn--full" position="right" style="margin: 10px 0px; font-size: 12px">
                                <div class="c-submitBtn__icon"><!---->
                                    <div>' . $row['shop_name'] . '</div>
                                </div>
                            </button>
                        </a>';
        }
        return $tmp;
    }

    /**
     * Draw header base link
     * And check it on head.php in designstore project
     *
     * @param $c
     * @return string
     */
    static function drawHeaderBaseLink($c)
    {
        $list_shop = getListShop();

        switch ($c[2]) {
            case 'dashboard':
                if ($list_shop) {
                    return sprintf('/proc.php?run=goToShop&shop=%s', $list_shop[0]['url']);
                } else {
                    return '/page.php?p=dashboard';
                }
                break;
            case 'pay':
            case 'balance':
            case 'analytics':
            case 'mail_template_magazine_store':
                if ($list_shop) {
                    return sprintf('%s?is_admin=true&session=%s&m_type=%s', $list_shop[0]['url'], Globals::session('DRAW_TOOL_SESSION'), $c[2]);
                } else {
                    return '#regist-shop" rel="modal:open';
                }
                break;
        }
    }

    /**
     * @param $c
     * @return string
     */
    static function drawSelectDomain($c)
    {
        global $sql;
        $tmp = '<select name="' . $c[2] . '">' . "\n";

        $col = explode("/", $c[3]);
        $val = explode("/", $c[4]);

        $data = [];
        if ($id = Globals::get("id")) {
            $data = $sql->keySelectRecord("personal_shop_info", "id", $id);
        }

        $count_col = count($col);
        for ($i = 0; $i < $count_col; $i++) {
            if (!empty($data)) $is_select = strpos($data['url'], $col[$i]) !== false;
            else $is_select = false;
            if (isset($data) && $is_select)
                $tmp .= '<option value="' . $col[$i] . '" selected>' . $val[$i] . '</option>' . "\n";
            else
                $tmp .= '<option value="' . $col[$i] . '">' . $val[$i] . '</option>' . "\n";
        }
        $tmp .= '</select>';
        return $tmp;
    }

    static function drawInfo($c, $data)
    {
        $items = Globals::getItems();

        if (!empty($items) && !empty($c[3]) && !empty($data[$c[3]]) && isset($items[$c[2]][$data[$c[3]]])) {
            return $items[$c[2]][$data[$c[3]]];
        }

        return '';
    }

    /**
     * Display popup stock item blank
     * @param $c
     * @return string
     */
    static function drawListCountItemSize($c)
    {
        global $sql;
        $tmp = '';
        $items = Globals::getItems('LIST_STOCK_ITEM');
        Globals::setSession('LIST_ITEM_BLANK', '');

        $where = $sql->setWhere('master_blank_item_price', null, 'item_type', '=', $c[2]);
        $result = $sql->getSelectResult('master_blank_item_price', $where);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (isset($items[trim($rec['item_type_sub'])][trim($rec['item_type_size'])])) {
                $items[trim($rec['item_type_sub'])][trim($rec['item_type_size'])]['price'] = number_format($rec['price']);
                $items[trim($rec['item_type_sub'])][trim($rec['item_type_size'])]['code'] = $rec['id'];
            }
        }

        foreach ($items as $sub => $data) {
            foreach ($data as $size => $value) {
                if ($value['stock'] > 0) {
                    $tmp .= sprintf('<tr>
                                                <td>%s</td>
                                                <td>%s</td>
                                                <td>%s</td>
                                                <td><input value="0" data-item="%s" data-size="%s" data-sub="%s" class="item-row" type="number"></td>
                                                <td>%s円</td>
                                                <td class="total_price_%s">0円</td>
                                                </tr>', $value['item_type_sub_name'], $value['item_type_size_name'], $value['stock'], $c[2], $size, $sub, $value['price'], $value['code']);
                }
            }
        }
        return $tmp;
    }

    static function getBreadCrumb($name, $page = '', $position = 2)
    {
        if (empty($page)) {
            $page = $_SERVER['REQUEST_URI'];
        }

        return sprintf('{
                            "@type": "ListItem",
                            "position": %s,
                              "item":
                              {
                                "@id": "%s",
                                "name": "%s" 
                              }
                        }', $position, $page, $name);
    }

    static function drawCategories($c)
    {
        global $sql;

        $category_html = '';
        $table = 'master_item_web_categories';
        $where = $sql->setWhere($table, null, 'state', '=', 1);
        $master_item_web_categories = $sql->getSelectResult($table, $where);

        while ($category = $sql->sql_fetch_assoc($master_item_web_categories)) {
            if (empty($c[2])) {
                $category_html .= sprintf('
                    <div class="list_li_nav">
                        <div class="item_li_nav">
                            <a href="/item-detail/category/%s">%s</a>
                        </div>
                    </div>', $category['name'], $category['name']);
            } else {
                $category_html .= sprintf('
                    <li class="nav-item">
						<a href="/item-detail/category/%s">%s</a>
					</li>', $category['name'], $category['name']);
            }
        }

        return $category_html;
    }

    static function itemCategories($c, $data)
    {
        global $sql;
        $link = '';

        if (!empty($c[2])) {
            if (in_array($c[2], ['item_type', 'id']) && !empty($data[$c[2]])) {
                $c[2] = $data[$c[2]];
            }

            $link = sprintf('/item-detail/%s', $c[2]);

            $category = Globals::getItems($c[2]);

            if (empty($category)) {
                $category = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT MIC.`name` mic_name, MISC.`name` misc_name
                                                        FROM item_web_categories IC
                                                        JOIN master_item_web_categories MIC ON MIC.id = IC.category
                                                        JOIN master_item_web_sub_categories MISC ON MISC.id = IC.sub_category
                                                        WHERE IC.is_main = 1 AND IC.item_type = "%s" LIMIT 1;', $sql->escape($c[2]))));
            }

            if (!empty($category)) {
                if (!empty($c[3])) {
                    if ($c[3] == 'drawParamCategory') {
                        $c[3] = Extension::drawParamCategory();
                    }

                    if (!empty($c[3])) {
                        $category['misc_name'] = $c[3];
                    }
                }

                $link = sprintf('/item-detail/%s/%s/%s',
                    str_replace(' ', '-', $category['mic_name']), str_replace(' ', '-', $category['misc_name']), $c[2]);
            }
        }

        return $link;
    }

    static function drawDisplayListItemCat2($c)
    {
        global $sql;
        $tmp = "";
        $table = 'master_item_web_sub_categories';

        $where = $sql->setWhere($table, null, 'parent', "=", $c[2]);
        $where = $sql->setWhere($table, $where, 'state', "=", 0);
        $result = $sql->getSelectResult($table, $where);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= sprintf('<section><h3 class="common-title_m">%s 一覧</h3><ul class="product-page-list-ver-1__ct">', $rec['name']);
            $tmp .= Extension::drawListItemByField([
                2 => 'item_web_categories',
                3 => 'sub_category',
                4 => $rec['id'],
                5 => 'item',
                6 => 'master_item_type',
                7 => 'item_type',
                8 => 'id',
                9 => 'master_item_web_sub_categories',
                10 => 'order_by',
                11 => 'sub_category'
            ]);
            $tmp .= '</ul></section>';
        }
        return $tmp;
    }

    static function drawRankingItemCategoryCompare($c)
    {
        global $sql;
        global $cc;
        $recommenitemhtml = '';

        $queryGet = 'select * from master_item_type INNER JOIN voices On master_item_type.id = voices.item_type WHERE master_item_type.category_id=' . $c[2] . ' GROUP BY 
voices.item_type ORDER BY voices.star DESC LIMIT 6';
        $i = 0;

        $result = $sql->rawQuery($queryGet);
        if ($result->num_rows >= 1) {
            while ($recItemType = $sql->sql_fetch_assoc($result)) {
                $linktool = Extension::getDrawToolLinkString(null, null, null, $recItemType["item_type"]);
                $recommenitemhtml .= '<li class="bl-hot open_modal_item" data-id="" style="cursor: pointer; width: calc(100%/3)"><div class="reItem4 itemnumber">
										<div style="padding-top: 5px" class="top-recomment-item" ><p>' . $recItemType["name"] . '</p><p>' . $recItemType["item_code_nominal"] . ' | ' . $recItemType["maker"] . '</p></div>
										<div style="margin: auto" class="bl-bigger">
										<a href="' . $linktool . '" target="_blank"><img class="lazyload" data-src="' . $recItemType["preview_url"] . '" width="150px" border="0" /></a></div>
										<div class="name_price"><p class="p_price"><i class="fa fa-check-circle" aria-hidden="true"></i>選択する</p>
										<p class="end_p"><label class="first_price">価格</label><label class="end_label">' . number_format($recItemType["tool_price"]) . '円~</label></p></div></div> </li>';
            }
        }
        return $recommenitemhtml;
    }

    static function countColorDefalt()
    {
        global $sql;
        $data = array();
        $tmp_item = '';
        $id = Globals::get('id');
        $img = '';

        $tmp_wheresearch = $sql->setWhere('master_item_type_sub', null, "item_type", "=", 'IT001');
        $tmp_order = $sql->setOrder('master_item_type_sub', null, 'is_main', 'DESC');
        $result = $sql->getSelectResult('master_item_type_sub', $tmp_wheresearch);
        $i = 0;

        if ($result->num_rows >= 1) {

            while ($sub_result = $sql->sql_fetch_assoc($result)) {
                $tmp_item .= '<i class="fa fa-square" data-color="' . $sub_result["color"] . '" style="color: ' . $sub_result["color"] . '"></i>';
                $i++;
            }
        }

        return $tmp_item;
    }

    static function drawPrintType($c, $data)
    {
        $type = '刺繍';

        if (in_array($data['product_type'], ['l', 'pl'])) {
            $type = 'レーザ';
        }

        return sprintf($c[2], $type);
    }

    static function drawItemBattleContent($c, $data)
    {
        if (strlen($data[$c[2]]) > 100) {
            $data[$c[2]] = sprintf('<p class="item-comment">%s<span class="dots">...</span><span class="more-comment-content">%s</span></p><button class="read-more-comment" type="button">もっと見る</button>', substr($data[$c[2]], 0, 100), substr($data[$c[2]], 100));
        }

        return $data[$c[2]];
    }

    static function drawItemComments($c, $data)
    {
        global $sql;

        $content = '<ul class="main-content-video">';
        $table = 'item_comments';
        $where = $sql->setWhere($table, null, 'item_id', '=', $data['id']);
        $where = $sql->setWhere($table, $where, 'status', '=', 1);
        $order = $sql->setOrder($table, null, 'created_at', 'DESC');
        $total = $sql->getRow($table, $where);

        if ($total > 0) {
            $comments = $sql->getSelectResult($table, $where, $order, [0, 3]);

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
                            </li>', $comment['comment'], DateTime::createFromFormat('Y-m-d H:i:s', $comment['created_at'])->format('Y年m月d日 H:i'), $comment['nickname']);
            }
        }

        $content .= '</ul>';

        if ($total > 3) {
            $content .= sprintf('<div class="link-detail-content-video">
                            <a href="#" class="bg-0097c8" id="load-more" data-current="%s">もっと見る</a>
                        </div>', 3);
        }

        return $content;
    }

    static function drawBattleRank($c)
    {
        global $sql;

        $data = '';
        $ranks = [
            1 => 'gold',
            2 => 'sliver',
            3 => 'bronze',
        ];
        $limit = 7;
        $offset = 0;
        $total = 0;

        if (!empty($c['offset'])) {
            $offset = $c['offset'];
        }

        if (!empty($c['limit'])) {
            $limit = $c['limit'];
        }

        if ($c[3] == 'user') {
            $query = sprintf('SELECT * FROM battle_rankings WHERE type = %s AND user_id NOT IN ("%s") ORDER BY total_item DESC LIMIT %s, %s;', $c[2], implode('","', CREATOR_ID), $offset, $limit);
        } else {
            $query = sprintf('SELECT * FROM battle_rankings WHERE type = %s AND user_id IN (SELECT id FROM user WHERE user_type = "%s") ORDER BY total_item DESC LIMIT %s, %s;', $c[2], $c[3], $offset, $limit);
        }

        $i = $offset + 1;
        $battle_rankings = $sql->rawQuery($query);

        while ($battle_rank = $sql->sql_fetch_assoc($battle_rankings)) {
            $total++;

            if ($total == 7 && $limit == 7) {
                continue;
            }

            if (in_array($i, array_keys($ranks))) {
                $data .= getBattleRank($battle_rank, $i, $ranks, true);
            } else {
                $data .= getBattleRank($battle_rank, $i, $ranks);
            }

            $i++;
        }

        if ($c[2] == 0 && $c[3] == 'user') {
            if ($limit == 7 && $total > 6) {
                $data .= sprintf('<tr class="total"><td></td><td><button class="load-more-battle-rank" data-type="%s" data-user-type="%s">もっと見る</button></td><td><a href="%s"><button class="btn-link-market">参加アイテム一覧</button></a></td></tr>', $c[2], $c[3], '/search.php?type=item&is_battle=1&tab=16');
            } else {
                $data .= '<tr class="total"><td></td><td><a href="/search.php?type=item&is_battle=1&tab=16"><button class="btn-link-market">参加アイテム一覧</button></a></td><td></td></tr>';
            }
        } else {
            if ($limit == 7 && $total > 6) {
                $data .= sprintf('<tr class="total"><td></td><td><button class="load-more-battle-rank" data-type="%s" data-user-type="%s">もっと見る</button></td><td></td></tr>', $c[2], $c[3]);
            }
        }

        return $data;
    }

    static function draw_type_rank($c)
    {
        global $sql;

        $data = '';
        $ranks = [
            1 => 'gold',
            2 => 'sliver',
            3 => 'bronze',
        ];
        $limit = self::TYPE_RANK_MAX;
        $offset = 0;
        $total = 0;

        if (!empty($c['offset'])) {
            $offset = $c['offset'];
        }

        if (!empty($c['limit'])) {
            $limit = $c['limit'];
        }

        $query = sprintf('SELECT * FROM type_ranks WHERE type = %s AND market_type = %s LIMIT %s OFFSET %s', $c[2], $c[3], $limit, $offset);

        $i = $offset + 1;
        $battle_rankings = $sql->rawQuery($query);

        while ($battle_rank = $sql->sql_fetch_assoc($battle_rankings)) {
            $total++;

            if ($total == self::TYPE_RANK_MAX && $limit == self::TYPE_RANK_MAX) {
                continue;
            }

            if (in_array($i, array_keys($ranks))) {
                $data .= getBattleRank($battle_rank, $i, $ranks, true);
            } else {
                $data .= getBattleRank($battle_rank, $i, $ranks);
            }

            $i++;
        }

        if ($c[2] == 0 && $c[3] == 'user') {
            if ($limit == self::TYPE_RANK_MAX && $total > self::TYPE_RANK_MAX - 1) {
                $data .= sprintf('<tr class="total"><td></td><td><button class="load-more-battle-rank" data-type="%s" data-user-type="%s">もっと見る</button></td><td><a href="%s"><button class="btn-link-market">参加アイテム一覧</button></a></td></tr>', $c[2], $c[3], '/search.php?type=item&is_battle=1&tab=16');
            } else {
                $data .= '<tr class="total"><td></td><td><a href="/search.php?type=item&is_battle=1&tab=16"><button class="btn-link-market">参加アイテム一覧</button></a></td><td></td></tr>';
            }
        } else {
            if ($limit == self::TYPE_RANK_MAX && $total > self::TYPE_RANK_MAX - 1) {
                $data .= sprintf('<tr class="total"><td></td><td><button class="load-more-battle-rank" data-type="%s" data-user-type="%s">もっと見る</button></td><td></td></tr>', $c[2], $c[3]);
            }
        }

        return $data;
    }

    static function draw_market_type_rank($c)
    {
        global $sql;

        $data = '';
        $ranks = [
            1 => 'gold',
            2 => 'sliver',
            3 => 'bronze',
        ];
        $max = self::TYPE_RANK_MAX;
        $limit = self::TYPE_RANK_MAX;
        $offset = 0;
        $total = 0;

        if (!empty($c['offset'])) {
            $offset = $c['offset'];
        }

        if (!empty($c['limit'])) {
            $limit = $c['limit'];
        }

        if (empty($c[4])) {
            $query = sprintf('SELECT * FROM type_ranks WHERE type = %s AND market_type = %s ORDER BY total_item DESC LIMIT %s, %s;', $c[2], $c[3], $offset, $limit);
        } else {
            if ($c[4] == 'original') {
                $query = sprintf('SELECT * FROM type_ranks WHERE type = %s AND market_type = %s AND user_id IN ("%s") ORDER BY total_item DESC LIMIT %s, %s;', $c[2], $c[3], implode('","', USER_CAMPAIGN[$c[5]]), $offset, $limit);
            } else {
                $query = sprintf('SELECT * FROM type_ranks WHERE type = %s AND market_type = %s AND user_id NOT IN ("%s") ORDER BY total_item DESC LIMIT %s, %s;', $c[2], $c[3], implode('","', USER_CAMPAIGN[$c[5]]), $offset, $limit);
            }
        }

        $i = $offset + 1;
        $type_ranks = $sql->rawQuery($query);

        while ($battle_rank = $sql->sql_fetch_assoc($type_ranks)) {
            $total++;

            if ($total == $max && $limit == $max) {
                continue;
            }

            if (in_array($i, array_keys($ranks))) {
                $data .= getBattleRank($battle_rank, $i, $ranks, true);
            } else {
                $data .= getBattleRank($battle_rank, $i, $ranks);
            }

            $i++;
        }

        if ($total > $max - 1) {
            $next = [$c[2]];

            if (!empty(Globals::getItems('show_more'))) {
                $next = array_merge(Globals::getItems('show_more'), $next);
            }

            Globals::setItems($next, 'show_more');
        }

        if (empty($data)) {
            return '<tr>該当データはありません。</tr>';
        }

        return $data;
    }

    static function draw_display_show_more($c)
    {
        $next = Globals::getItems('show_more');

        if (!empty($next)) {
            $type = '';

            foreach ($next as $user_type) {
                if ($user_type == 1) {
                    $type .= sprintf(' data-user="1"');
                } else {
                    $type .= sprintf(' data-item="1"');
                }
            }

            if (!empty($c[2])) {
                if ($c[2] == 'uuumfes') {
                    return sprintf('<div class="btn btn-red-2">
                            <button id="load-more-rank"%s>もっと見る</button>
                        </div>', $type);
                }
            }
            return sprintf('<div class="btn blue">
                            <button id="load-more-rank"%s>もっと見る</button>
                        </div>', $type);
        }
    }

    static function drawMessages()
    {
        global $sql;

        $tweets = '';
        $a_tweet = '<div class="column-battle"><div class="item bg-ecf9fd"><blockquote class="twitter-tweet" data-lang="ja"><a href="https://twitter.com/%s/status/%s"></a></blockquote></div></div>';
        $tweet_tags = $sql->rawQuery(sprintf('SELECT * FROM tweet_tags WHERE `type` = %s', Globals::get('type')));

        foreach ($tweet_tags as $tweet_tag) {
            $tweets .= sprintf($a_tweet, $tweet_tag['tweet_screen_name'], $tweet_tag['tweet_id']);
        }

        return $tweets;
    }

    static function drawMarketMessage()
    {
        $message = 'お笑いTシャツバトル';

        if (empty(Globals::session('IS_BATTLE_USER'))) {
            $message = 'マーケット販売に';
        }

        return $message;
    }

    static function drawUserOfPurchasedBattleItem()
    {
        global $sql, $device_path;

        $item_html = '<div class="note bg-e8e8e8 note-marquee">';
        $pays = $sql->rawQuery('SELECT SUM(pay_item.item_row) AS total, `user`.`name`, `user`.full_name, pay.regist_unix FROM pay_item
JOIN pay ON pay.id = pay_item.pay AND pay.pay_state = 1
JOIN item ON item.id = pay_item.item AND battle_time > 0
JOIN `user` ON `user`.id = item.`user` AND `user`.state = 1
WHERE pay_item.regist_unix > 1618412400
GROUP BY pay.id
HAVING total > 0
ORDER BY
pay.regist_unix DESC
LIMIT 10');

        if (!empty($pays->num_rows)) {
            while ($pay = $sql->sql_fetch_assoc($pays)) {
                if (empty($pay['full_name'])) {
                    $pay['full_name'] = $pay['name'];
                }

                $item_html .= sprintf('%s %sのTシャツが%s枚購入されました。', date('Y年m月d日 H:i', $pay['regist_unix']), $pay['full_name'], $pay['total']) . "<span style='margin-right: 350px;'></span>";
            }
        }

        $item_html .= "</div>";
        return $item_html;
    }

    static function draw_purchased_market_type_item($c)
    {
        global $sql;

        $item_html = '<div class="note bg-e8e8e8 note-marquee">';
        $pays = $sql->rawQuery(sprintf('SELECT SUM(pay_item.item_row) AS total, `user`.`name`, `user`.full_name, pay.regist_unix FROM pay_item
            JOIN pay ON pay.id = pay_item.pay AND pay.pay_state = 1 AND pay.delivery_state != 2
            JOIN item ON item.id = pay_item.item AND type_time > 0 AND market_type = %s
            JOIN `user` ON `user`.id = item.`user` AND `user`.state = 1
            WHERE pay_item.regist_unix >= 1626793200 GROUP BY pay.id HAVING total > 0
            ORDER BY pay.regist_unix DESC LIMIT 10', (int)$c[2]));

        if (!empty($pays->num_rows)) {
            while ($pay = $sql->sql_fetch_assoc($pays)) {
                if (empty($pay['full_name'])) {
                    $pay['full_name'] = $pay['name'];
                }

                $item_html .= sprintf('%s %sのTシャツが%s枚購入されました。', date('Y年m月d日 H:i', $pay['regist_unix']), $pay['full_name'], $pay['total']) . "<span style='margin-right: 350px;'></span>";
            }
        } else {
            $item_html .= '該当データはありません。';
        }

        $item_html .= "</div>";
        return $item_html;
    }

    static function drawTwitterHashtagLink($c)
    {
        global $sql;

        $link = '#';
        $other_tag = '';
        $user = Globals::getUsers($c[2]);

        if (empty($user)) {
            $user = $sql->selectRecord('user', $c[2]);
        }

        if (!empty($c[3])) {
            $other_tag = sprintf(' #%s', $c[3]);
        }

        if (!empty($user)) {
            $link = sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf('%s/info.php?type=user&id=%s
#t1グランプリ #%s%s #みんなで応援購入だ', ApiConfig::DOMAIN, $user['id'], str_replace(['【', '】', ' '], ['', '', '-'], $user['name']), $other_tag)));
        } else {
            $link = sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf('%s/market/%s
#t1グランプリ #%s%s #みんなで応援購入だ', ApiConfig::DOMAIN, $c[2], str_replace(['【', '】', ' '], ['', '', '-'], $c[4]), $other_tag)));
        }

        return $link;
    }

    static function draw_tweet_link($c)
    {
        return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf('%s/info.php?type=user&id=%s
%s', ApiConfig::DOMAIN, $c[2], TWEET_TAG[$c[2]])));
    }

    static function draw_is_battle($c, $data)
    {
        $is_battle = '';

        if (in_array($data['id'], CREATOR_ID)) {
            $is_battle = '&is_battle=1';
        } else {
            global $sql;
            $item = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT id FROM item WHERE battle_time AND user = "%s" > 0 LIMIT 1', $data['id'])));

            if (!empty($item)) {
                $is_battle = '&is_battle=1';
            }
        }

        return $is_battle;
    }

    static function drawBattleItems()
    {
        global $sql;

        $item_string = '';
        $item_query = 'SELECT user.`name`, item.item_preview1, item.id, item.name item_name, item.user FROM item
JOIN `user` ON item.`user` = `user`.id
WHERE item.state = 1 AND `user`.user_type > 0 AND item.buy_state = 1 AND item.regist_unix > 0 AND `user`.user_type < 7';
        $an_item_string = '<div class="column-battle">
                    <div class="item bg-fff">
                        <div class="image item">
                            <img class="lazyload"
                                 data-src="%s"
                                 alt="%s">
                        </div>
                        <div class="title">%s</div>
                        <a href="%s" target="_blank">
                            <button class="bg-00aeff">ツイートして応援</button>
                        </a>
                        <a href="/market/%s">
                            <button class="bg-cc0000">Tシャツを購入</button>
                        </a>
                    </div>
                </div>';

        $items = $sql->rawQuery($item_query);

        foreach ($items as $item) {
            $item_string .= sprintf($an_item_string, $item['item_preview1'], $item['item_name'], $item['name'], self::drawTwitterHashtagLink([2 => $item['user']]), $item['id']);
        }

        return $item_string;
    }

    static function drawImageBanner($c)
    {
        global $sql;
        $table = 'banner_schedule';
        $tmp = '';

        $where = $sql->setWhere($table, null, 'state', '=', 1);
        $order = $sql->setOrder($table, null, 'id', 'DESC');
        $result = $sql->getSelectResult($table, $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($c[2] == 'image_pc') {
                $tmp .= sprintf('<li><a href="%s"><img class="lazyload" data-src="%s" alt="%s"></a></li>', $rec['url'], $rec[$c[2]], $rec['alt']);
            } else {
                $tmp .= sprintf('<div class="vacation-box box_face_mask" style="text-align: center;">
                                            <a href="%s" style="display: inline-block;margin-bottom: 5px">
                                                                                            <img class="lazyload" data-src="%s" alt="%s" border="0"/>
                                                                                        </a>
                                                                                        </div>', $rec['url'], $rec[$c[2]], $rec['alt']);
            }
        }

        return $tmp;
    }

    static function drawListIdol($c)
    {
        global $sql;
        global $cc;
        $table = 'user';
        $tmp = '';

        $where = $sql->setWhere($table, null, 'user_type', '=', 7);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $order = $sql->setOrder($table, null, 'id');

        $template = SystemUtil::getPartsTemplate('user', 'idol');
        $result = $sql->getSelectResult($table, $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $rec['item_type'] = $c[2];
            $tmp .= $cc->run($template, $rec);
            Globals::setUsers($rec, $rec['id']);
        }

        return $tmp;
    }

    static function drawItemIdol($c)
    {

        global $sql;
        global $cc;
        $table = 'item';
        $tmp = '';

        $where = $sql->setWhere($table, null, 'user', '=', $c[2]);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "item_type", "=", $c[3]);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
        $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
        $order = $sql->setOrder($table, null, 'regist_unix', 'DESC');

        $template = SystemUtil::getPartsTemplate('user', 'item');
        $result = $sql->getSelectResult($table, $where, $order);

        $i = 0;
        $items = [];
        while ($rec = $sql->sql_fetch_assoc($result)) {
            if (empty($rec['owner_item'])) {
                if ($i == 0) {
                    $tmp .= $cc->run($template, $rec);
                    $i++;
                } else {
                    array_unshift($items, $rec);
                }
            }
        }

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                $tmp .= $cc->run($template, $item);
            }
        }

        return $tmp;
    }

    static function drawTwitterLink($c)
    {
        global $sql;

        $user = Globals::getUsers($c[2]);

        if (empty($user)) {
            $user = $sql->selectRecord('user', $c[2]);
        }

        return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf('%sほくりく、みんなで応援！│ %s/info.php?type=user&id=%s', $user['name'], ApiConfig::DOMAIN, $user['id'])));
    }

    static function drawListItemIdolByMonth($c)
    {
        global $sql;
        global $cc;
        $tmp = '';

        $template = SystemUtil::getPartsTemplate('user', 'item1');

        $query = 'SELECT 
                    *,
                    DATE_FORMAT( FROM_UNIXTIME( `regist_unix` ), ' . "'%s'" . ' ) AS `month` 
                  FROM
                    item 
                  WHERE
                    `user` IN ( SELECT id FROM `user` WHERE user_type = 7 ) 
                    AND state = 1 AND buy_state = 1 AND 2nd_owner_state = 1 AND regist_unix > 0 AND item_type = "%s"
                  HAVING
                    month = %s';
        $result = $sql->rawQuery(sprintf($query, '%c', $c[3], $c[2]));

        while ($rec = $sql->sql_fetch_assoc($result)) {
//            if (!empty($c[3]) && $c[3] != $rec['item_type']) {
//                continue;
//            }
            if (empty($rec['owner_item'])) {
                $tmp .= $cc->run($template, $rec);
            }
        }

        return $tmp;
    }

    static function drawTemplateAsset($c)
    {
        global $sql;

        $table = 'item_assets';
        $tmp = '';

        $where = $sql->setWhere($table, null, 'item_type', '=', $c[2]);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $result = $sql->getSelectResult($table, $where);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $contents = json_decode($rec['content'], true);
            foreach ($contents as $key => $content) {
                if (empty($content['state'])) {
                    continue;
                }
                $asset_template = $sql->selectRecord('asset_template', $content['template_id']);
                $data = base64_decode($asset_template['content']);
                $data = str_replace("title-header", $content['title'], $data);
                $data = str_replace("description", $content['description'], $data);

                if (is_array($content['image'])) {
                    for ($i = 1; $i <= count($content['image']); $i++) {
                        $data = str_replace("image" . $i, $content['image'][$i - 1], $data);
                        $data = str_replace("alt-text" . $i, $content['alt-text'][$i - 1], $data);
                    }
                } else {
                    $data = str_replace("image1", $content['image'], $data);
                    $data = str_replace("alt-text1", $content['alt-text'], $data);
                }
                $tmp .= $data . "<br>";
            }
        }

        return "<br>" . $tmp;
    }

    static function drawUserItems($c, $data)
    {
        global $sql, $device_path;

        $content = '';
        $table = 'item';
        $user_items = [];
        $item_type_ids = [];
        $item_type_sub_ids = [];
        $where = $sql->setWhere($table, null, 'user', '=', $data['user']);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, 'id', '!=', $data['id']);
        $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
        $where = $sql->setWhere($table, $where, 'buy_state', '=', 1);
        $where = $sql->setWhere($table, $where, '2nd_owner_state', '=', 1);
        $order = $sql->setOrder($table, null, "regist_unix", "DESC");
        $items = $sql->getSelectResult($table, $where, $order, [0, 7]);

        if ($items->num_rows) {
            $user = $sql->selectRecord('user', $data['user']);
            if ($user) {
                $content .= sprintf('<h2 class="common-title_s1">%sの他の商品</h2>', $user['name']);
            }

            $more = '';
            $item_content = '';
            $content .= '<div class="wrap-creator-list wrap-other-products-list"><div class="box-creator-list">%s</div>%s</div>';
            $an_item_content = '<div class="creator-list-item">
                                    <a href="/market/%s">
                                        <div class="items">
                                            <div class="item">
                                                <div class="text-name">%s</div>
                                                <div class="image"><img class="lazyload" data-src="%s" alt="%s"></div>
                                            </div>
                                        </div>
                                    </a>
                                </div>';

            foreach ($items as $key => $item) {
                $user_items[$key] = $item;

                if (!in_array($item['item_type'], array_keys(ITEM_PREVIEW))) {
                    $imagick_name = sprintf('%s_%s_imagick', $item['item_type'], $item['item_type_sub']);
                    $item_type_ids[$key] = $item['item_type'];
                    $item_type_sub_ids[$key] = $item['item_type_sub'];
                    Globals::setItems(0, $imagick_name);
                }

                if ($key == 5) {
                    break;
                }
            }

            if ($item_type_ids && $item_type_sub_ids) {
                $imagick_table = 'master_item_type_imagick';
                $where = $sql->setWhere($imagick_table, null, 'item_type', 'IN', $item_type_ids);
                $where = $sql->setWhere($imagick_table, $where, 'item_type_sub', 'IN', $item_type_sub_ids);
                $imagicks = $sql->getSelectResult($imagick_table, $where);

                foreach ($imagicks as $imagick) {
                    $imagick_name = sprintf('%s_%s_imagick', $imagick['item_type'], $imagick['item_type_sub']);
                    Globals::setItems(1, $imagick_name);
                }
            }

            foreach ($user_items as $key => $item) {
                $item_preview = '';
                get_item_previews($item, 1);

                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($item["item_preview{$i}"])) {
                        $item_preview = $item["item_preview{$i}"];
                        break;
                    }
                }

                $item_content .= sprintf($an_item_content, $item['id'], $item['name'], $item_preview, $item['name']);

                if ($key == 5) {
                    break;
                }
            }

            if ($items->num_rows > 6) {
                $more = sprintf('<div class="btn-market">
                    <a href="/search.php?type=item&user=%s" class="btn-red btn-creator-list">
                        他の商品をもっと見る%s
                    </a>
                </div>', $data['user'], $device_path == 'pc/' ? '' : '<span class="image"><img class="lazyload" data-src="/common/design/user/img/kamaitachi/icon-arrow-right.png" alt="かまいたち"></span>');
            }

            $content = sprintf($content, $item_content, $more);
        }

        return $content;
    }

    static function drawUsers()
    {
        global $sql, $device_path;

        $br = '';
        $user_content = '';
        $lazy_image = 'data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20210%20140%22%3E%3C/svg%3E';
        $an_user_content = '<div class="creator-list-item">
                                <a href="/info.php?type=user&id=%s">
                                    <div class="items">
                                        <div class="item">
                                            <div class="image"><img class="lazyload" data-src="%s" src="%s" alt="%s"></div>
                                            <div class="text-name">%s%s</div>
                                        </div>
                                    </div>
                                </a>
                            </div>';
        $users = $sql->rawQuery('SELECT * FROM user_ranks');

        if ($device_path != 'pc/') {
            $br = '<br/>';
        }

        foreach ($users as $user) {
            $avatar = '/common/design/user/img/creator_noimage.gif';

            if ($user['profile_image']) {
                $avatar = $user['profile_image'];
            }

            $user_content .= sprintf($an_user_content, $user['id'], $avatar, $lazy_image, $user['name'], $br, $user['name']);
        }

        return $user_content;
    }

    static function draw_preload_previews()
    {
        if ($_SERVER['SCRIPT_NAME'] == '/index.php') {
            $preloads = '<link rel="preload" as="image" href="https://appimg.chatplus.jp/app/3946/eyecatcher/eyecatcher_sp.png" imagesrcset="https://up-t.jp/common/img/sp/icon-talk-1-sp.png 200w" imagesizes="50vw"/>';

            return $preloads;
        }
    }

    static function drawAssetTemplateEdit($c, $data)
    {
        global $cc;
        $tmp = '';

        $template = SystemUtil::getPartsTemplate("other", 'asset_template');
        $data = json_decode($data['content'], true);
        foreach ($data as $key => $val) {
            $tmp_input = drawInputAssetTemplate($val['template_id'], $val);
            $rec['input_template'] = $tmp_input;
            $rec['template_id[]'] = $val['template_id'];
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function drawUUUMUserItems($c)
    {
        global $sql;

        $item_html = '';
        $an_item_html = '<div class="item">
                            <div class="left">
                                <div class="image">
                                    <a href="/market/%s">
                                        <img src="%s" alt="%s">
                                    </a>
                                </div>
                            </div>
                            <div class="right">
                                <div class="title">%s</div>
                                <div class="price">¥%s</div>
                                <div class="content">
                                    %s
                                </div>
                                <div class="btn red">
                                    <a href="/market/%s">
                                        <span>購入</span>
                                    </a>
                                </div>
                            </div>
                        </div>';

        $item_query = 'SELECT
                            item.*
                        FROM
                            item
                            JOIN master_item_type MIT ON MIT.id = item.item_type
                        WHERE
                            `user` = "%s" AND item.state = 1
                            AND item.regist_unix > 0 AND item.type_time > 0
                            AND item.market_type = 1 AND item.buy_state = 1
                            AND item.2nd_owner_state = 1
                        ORDER BY
                            MIT.item_order DESC,
                            item.type_time DESC,
                            item.regist_unix DESC';

        $items = $sql->rawQuery(sprintf($item_query, $c[2]));

        if ($items->num_rows) {
            foreach ($items as $item) {
                $item_html .= sprintf($an_item_html, $item['id'], get_item_preview($item), $item['name'], $item['name'], number_format($item['price']), $item['item_text'], $item['id']);
            }
        }

        return $item_html;
    }

    static function draw_market_type_items($c)
    {
        $item_html = '';
        $is_all = false;
        $is_next = true;
        $button_html = '';
        $type_ids = [];
        $tag = 'ユニフォームグランプリ';
        $image_view_more = '/common/design/user/img/uuum/arrow-right-bl.png';
        $div_view_more = '<div class="btn">%s</div>';
        $lazy_image = 'src="data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20210%20140%22%3E%3C/svg%3E"';

        if ($c[3] > 1) {
            $tag = $c[5];
            $image_view_more = '/common/design/user/img/kurokora/icon-arrow-right-gray.png';
            $div_view_more = '<div class="box-post-design"><div class="btn-post-design">%s</div></div>';
        }

        $item_text = '<div class="item">
                        <a href="/market/%s">
                            <div class="image">
                            <img class="lazyload" %s data-src="%s" alt="%s">
                            </div>
                        </a>
                    </div>';
        $url = sprintf('/search.php?type=item&keyword=%s', $tag);
        $button = '<a href="%s">
                        <span>もっとみる</span>
                        <span class="image">
                            <img class="lazyload" %s data-src="%s" alt="%s">
                        </span>
                    </a>';
        $content = '<div class="item-content">
                        %s
                    </div>%s';

        if (!isset($c['current'])) {
            $c['current'] = 0;
        }

        $c['limit'] = 9;

        if (!in_array($c[2], array_keys(UUUM_ITEMS))) {
            if ($c[2] == ALL) {
                $is_all = true;
            } else {
                $is_next = false;
            }
        } else {
            $type_ids = UUUM_ITEMS[$c[2]];
            $url .= sprintf('&tab_type=%s', $c[2]);
        }

        if ($is_next) {
            $items = get_market_type_items($type_ids, $c['limit'], $c['current'], $c[3], $is_all);

            foreach ($items as $key => $item) {
                if ($key === $c['limit'] - 1) {
                    $button_html = sprintf($div_view_more, sprintf($button, $url, $lazy_image, $image_view_more, $tag));
                    break;
                }

                $item_html .= sprintf($item_text, $item['id'], $lazy_image, get_item_preview($item), $tag);
            }
        }

        if ($c['current'] === 0) {
            if (empty($item_html)) {
                return '該当データはありません。';
            }

            return sprintf($content, $item_html, $button_html);
        }

        return ['item' => $item_html, 'button' => $button_html];
    }

    static function draw_entry_url()
    {
        if (Globals::session('LOGIN_TYPE') == 'nobody') {
            return '/regist.php?type=user';
        }

        return '/search.php?type=item&design=my';
    }

    static function draw_user_search($c)
    {
        global $sql;

        if ($c[2] === 'pc') {
            $search = '<tr>
                       <th>%s</th>
                       <td><label><input type="checkbox" name="%s" value="%s" checked>%s</label></td>
                   </tr>';
        } else {
            $search = '<dt>%s</dt>
                       <dd>
                           <label><input type="checkbox" name="%s" value="%s" checked>%s</label>
                       </dd>';
        }

        $search_html = '';

        $accept_types = ['user' => 'ユーザー', 'owner' => 'オーナー'];

        foreach ($accept_types as $type => $name) {
            if (!empty(Globals::get($type))) {
                $user = $sql->selectRecord('user', Globals::get($type));

                $search_html .= sprintf($search, $name, $type, Globals::get($type), $user['name']);
            }
        }

        return $search_html;
    }

    static function draw_mask_price($c, $data)
    {
        $total_price = 0;
        $price_type = '';
        $price_string = '';

        $total_price_string = '<dl>
					<div class="sub_total pt7">
						小計（税込み）<br>
						%s円	(%s)
					</div>
				</dl>';

        $prices = get_mask_prices(get_mask_quantity($data))['prices'];

        foreach ($prices as $price) {
            if (!empty($price_type)) {
                $price_type .= '+';
            }

            $total_price += $price['price'] * $price['total'];
            $price_type .= sprintf('%s円×%s枚', number_format($price['price']), $price['total']);

            $price_string .= sprintf('<dl style="padding: 5px 12px;">
                                                <dt>アイテム単価  :</dt>
                                                <dd>%s円（%s枚）</dd>
                                            </dl>', number_format($price['price']), $price['total']);

        }

        return $price_string . sprintf($total_price_string, $total_price, $price_type);
    }

    static function draw_cart_total_tax_price($c, $data)
    {
        global $cc;

        $cod = 0;
        $design = 0;
        $postage = 0;
        $pay_point = 0;
        $pay_type_add = 0;
        $mask_quantity = 0;
        $tax = 1 + getTaxRate();
        $discounts = getCartDiscount();
        $gift_total = Extension::drawCartGiftTotale();
        $item_total = getCartPrice();
        $cart_discount = $discounts['discount'];
        $rank_discount = Extension::get_discount_for_rank();
        $discount_student = $discounts['student'];
        $discount_promotion_code = $cc->getVariable('discount_promotion_code');

        foreach (Globals::session('CART_ITEM') as $cart) {
            $mask_quantity += get_mask_quantity($cart);
        }

        $mask_price = get_mask_prices($mask_quantity)['total'];
        $item_total -= $mask_price;

        if (!empty($c[2]) && $c[2] == 'total') {
            $cod = Extension::getCod($c, $data);
            $design = Extension::designPrice([2 => 'design']);
            $postage = Extension::getPostage($c, $data);
            $pay_point = $data['pay_point'];
            $pay_type_add = $data['pay_type_add'];
        }

        return number_format(($item_total - $pay_point - $discount_promotion_code + $postage + $cod - $cart_discount - $discount_student + $gift_total + $pay_type_add + $design - $item_total * $rank_discount / 100) * $tax + $mask_price);
    }

    static function is_periodic_user()
    {
        if (Globals::session('IS_PERIODIC_USER')) {
            return 1;
        }

        return 0;
    }

    function draw_delivery_fee($c, $data)
    {
        if ($data['pay_type'] === 'after2') {
            $fee = getFeePostPay($data['pay_type']);
        } else {
            $fee = getCod($data['pay_type']);
        }

        return number_format($fee);
    }

    static function drawInputProductType()
    {
        $input = '';
        if (!empty(Globals::get('product_type'))) {
            $input = '<input type="hidden" name="product_type" value="' . Globals::get('product_type') . '" />';
        }
        return $input;
    }

    static function draw_hidden_reload()
    {
        if (Globals::get('reload') == 1) {
            return '<input type="hidden" id="reload" value="1">';
        }
    }

    static function drawListColorCurrent($c, $data)
    {
        global $sql;
        $content = "<select name='item_type_sub'>";
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table, null, 'item_type', '=', $data['item_type']);
        $where = $sql->setWhere($mit_sub_table, $where, 'state', '=', 1);

        $result_mit_sub = $sql->getSelectResult($mit_sub_table, $where);
        if ($result_mit_sub->num_rows) {
            while ($mit_sub_rec = $sql->sql_fetch_assoc($result_mit_sub)) {
                if ($mit_sub_rec['id'] == $data['item_type_sub']) {
                    $content .= "<option value='{$mit_sub_rec['id']}' selected>{$mit_sub_rec['name']}</option>";
                } else {
                    $content .= "<option value='{$mit_sub_rec['id']}'>{$mit_sub_rec['name']}</option>";
                }
            }
        }

        $content .= "</select>";

        return $content;
    }

    static function drawListSideCurrent($c, $data)
    {
        global $sql;
        $content = "<select name='side_name'>";
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table, null, 'item_type', '=', $data['item_type']);
        $where = $sql->setWhere($mit_sub_table, $where, 'is_main', '=', 1);
        $where = $sql->setWhere($mit_sub_table, $where, 'state', '=', 1);

        $result_mit_sub = $sql->getSelectResult($mit_sub_table, $where);
        if ($result_mit_sub->num_rows) {
            $mit_sub_rec = $sql->sql_fetch_assoc($result_mit_sub);
        }

        if ($mit_sub_rec) {
            $mit_sub_side_table = 'master_item_type_sub_sides';
            $where = $sql->setWhere($mit_sub_side_table, null, 'color_id', '=', $mit_sub_rec['id']);
            $where = $sql->setWhere($mit_sub_side_table, $where, 'state', '=', 1);
            $list_side = $sql->getSelectResult($mit_sub_side_table, $where);

            while ($mit_sub_side_rec = $sql->sql_fetch_assoc($list_side)) {
                if ($mit_sub_side_rec['side_name'] == $data['side_name']) {
                    $content .= "<option value='{$mit_sub_side_rec['side_name']}' selected>{$mit_sub_side_rec['title']}</option>";
                } else {
                    $content .= "<option value='{$mit_sub_side_rec['side_name']}'>{$mit_sub_side_rec['title']}</option>";
                }
            }
        }

        $content .= "</select>";

        return $content;
    }

    static function drawListImageReview($c, $data)
    {
        $content = '';
        for ($i = 1; $i <= 4; $i++) {
            if (!empty($data["review_image{$i}"])) {
                $content .= "<a href='{$data["review_image{$i}"]}' data-lightbox='{$data['id']}'>
                                <img src='{$data["review_image{$i}"]}'>
                            </a>";
            }
        }
        return $content;
    }

    static function drawSideNameImagick($c, $data)
    {
        global $sql;
        $name = '';
        $mit_sub_table = 'master_item_type_sub';
        $where = $sql->setWhere($mit_sub_table, null, 'item_type', '=', $data['item_type']);
        $where = $sql->setWhere($mit_sub_table, $where, 'is_main', '=', 1);
        $where = $sql->setWhere($mit_sub_table, $where, 'state', '=', 1);

        $result_mit_sub = $sql->getSelectResult($mit_sub_table, $where);
        if ($result_mit_sub->num_rows) {
            $mit_sub_rec = $sql->sql_fetch_assoc($result_mit_sub);
        }

        if ($mit_sub_rec) {
            $mit_sub_side_table = 'master_item_type_sub_sides';
            $where = $sql->setWhere($mit_sub_side_table, null, 'color_id', '=', $mit_sub_rec['id']);
            $where = $sql->setWhere($mit_sub_side_table, $where, 'state', '=', 1);
            $list_side = $sql->getSelectResult($mit_sub_side_table, $where);

            while ($mit_sub_side_rec = $sql->sql_fetch_assoc($list_side)) {
                if ($mit_sub_side_rec['side_name'] == $data['side_name']) {
                    $name = $mit_sub_side_rec['title'];
                }
            }
        }

        return $name;
    }

    static function draw_admin_view_js()
    {
        if ($_SERVER['SCRIPT_NAME'] !== '/index.php' && !is_detail_page()) {
            return '<script type="text/javascript" src="/common/js/admin-view-item.js?v=1.4"></script>';
        }
    }

    static function drawListUser($c)
    {
        global $sql;
        global $cc;

        $items = $sql->rawQuery('SELECT id, `user` FROM item WHERE `user` IN (SELECT id FROM `user` WHERE user_type = 9 AND state = 1) AND state = 1 and buy_state =1 AND regist_unix > 0 GROUP BY `user`');

        foreach ($items as $item) {
            Globals::setUsers($item['id'], $item['user']);
        }

        $table = 'user';
        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $clume = $sql->setClume($table, $clume, 'profile_image');

        $where = $sql->setWhere($table, null, "state", "=", 1);
        $where = $sql->setWhere($table, $where, "search_state", "=", 1);
        $where = $sql->setWhere($table, $where, "user_type", "=", $c[3]);
        $order = $sql->setOrder($table, null, "sort_by");
        $result = $sql->getSelectResult($table, $where, $order, null, $clume);

        $template = SystemUtil::getPartsTemplate($c[2], 'user');

        $tmp = "";
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $rec['group_name'] = '';
            foreach (USER_ATJAM as $group_name => $users) {
                if (in_array($rec['id'], $users)) {
                    $rec['group_name'] = $group_name;
                    break;
                }
            }

            $tmp .= $cc->run($template, $rec);

        }

        return $tmp;
    }

    static function drawTweetLinkAtJam($c, $data)
    {
        if ($c[2] == 'user') {
            $user_id = $data['id'];
            $user_name = $data['name'];
            $item_id = Globals::getUsers($data['id']);
            $url = sprintf('%s/info.php?type=user&id=%s', ApiConfig::DOMAIN, $data['id']);

            if (!empty($item_id)) {
                $url = sprintf('%s/market/%s', ApiConfig::DOMAIN, $item_id);
            }
        } else {
            $user_id = $data['user'];
            $user_name = $data['user_name'];
            $url = sprintf('%s/market/%s', ApiConfig::DOMAIN, $data['id']);
        }

        $group_name = '';
        $tag_group_name = '';
        foreach (USER_ATJAM as $key => $users) {
            if (in_array($user_id, $users)) {
                $group_name = $key . 'の';
                $tag_group_name = '#' . $key;
                break;
            }
        }

        if (empty($data['type'])) {
            $data['type'] = '';
        }

        return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf('%s%sさんがデザインした限定%sを販売！！Twitter応援で雑誌モデルを目指せ！%s #THEFASHIONISTA #up_t #アットジャム %s #%s THE FASHIONISTAの詳細こちら！%s', $group_name, $user_name, $data['type'], $url, str_replace(' ', '', $tag_group_name), str_replace(' ', '', $user_name), ApiConfig::DOMAIN . '/atjam')));
    }


    static function drawListItem($c)
    {
        global $sql;
        global $cc;
        $table = 'item';
        $tmp = '';

        $template = SystemUtil::getPartsTemplate($c[2], 'item');

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'item_preview1');
        $clume = $sql->setClume($table, $clume, 'item_preview2');
        $clume = $sql->setClume($table, $clume, 'item_preview3');
        $clume = $sql->setClume($table, $clume, 'item_preview4');
        $clume = $sql->setClume('user', $clume, 'id', null, 'user');
        $clume = $sql->setClume('user', $clume, 'name', null, 'user_name');

        $where = $sql->setWhere('user', null, 'user_type', '=', $c[3]);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, 'item_type', '=', $c[4]);
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
        $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
        $where = $sql->setWhere($table, $where, 'owner', '=', '');

        $order = $sql->setOrder('user', null, 'sort_by');

        $inner_join = $sql->setInnerJoin('user', $table, 'user', 'user', 'id');

        $result = $sql->getSelectResult($table, $where, $order, null, $clume, null, $inner_join);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $rec['group_name'] = '';

            if (isset($rec['item_preview1']) && $rec['item_preview1']) {
                $rec['image_preview'] = $rec['item_preview1'];
            } else if (isset($rec['item_preview2']) && $rec['item_preview2']) {
                $rec['image_preview'] = $rec['item_preview2'];
            } else if (isset($rec['item_preview3']) && $rec['item_preview3']) {
                $rec['image_preview'] = $rec['item_preview3'];
            } else if (isset($rec['item_preview4']) && $rec['item_preview4']) {
                $rec['image_preview'] = $rec['item_preview4'];
            }

            foreach (USER_ATJAM as $group_name => $users) {
                if (in_array($rec['user'], $users)) {
                    $rec['group_name'] = $group_name;
                    break;
                }
            }

            $rec['type'] = $c[5];
            $rec['name'] = sprintf('%s %sさんがデザインした%s', $rec['group_name'], $rec['user_name'], $rec['type']);
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function draw_lespros_items($c)
    {
        $items_html = '';
        $other_items = [];
        $white_items = [];
        $white_item_id = '613ef399240d0';
        $user_items = [0 => [], 1 => [], 2 => [], 3 => []];
        $other_item_ids = ['613ef5c92c543', '613ee4b00a896', '613eef438bb9a', '613eee9053f0e'];
        $user_ids = constants::LESPROS_USER_IDS;
        $items = Globals::getItems('lespros_items');
        $tweet_text = 'モデルが、かわいいカッコイイデザインのグッズを販売！購入して応援しよう！
https://up-t.jp/lespros

#up_t  #モデル  #%s';
        $item_html = '<div class="lespros-2-item">
                        <img class="lazyload" data-src="%s" src="%s" alt="%s">
                        <p>%s</p>
                        <a href="/market/%s">もっと見る<span><i class="fa fa-chevron-right" aria-hidden="true"></i></span></a>
                        <a href="%s" class="twitter-button" target="_blank">ツイートして応援</a>
                    </div>';
        $lazy_load = 'data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20210%20140%22%3E%3C/svg%3E';

        if (empty($items)) {
            global $sql;

            $items = [];
            $table = 'item';
            $clume = $sql->setClume($table, null, 'id');
            $clume = $sql->setClume($table, $clume, 'user');
            $clume = $sql->setClume($table, $clume, 'name');
            $clume = $sql->setClume($table, $clume, 'item_preview1');
            $clume = $sql->setClume($table, $clume, 'item_preview2');
            $clume = $sql->setClume($table, $clume, 'item_preview3');
            $clume = $sql->setClume($table, $clume, 'item_preview4');
            $clume = $sql->setClume('user', $clume, 'name', null, 'user_name');

            $where = $sql->setWhere($table, null, 'user', 'IN', constants::LESPROS_USER_IDS);
            $where = $sql->setWhere($table, $where, 'state', '=', 1);
            $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
            $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
            $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);

            $order = $sql->setOrder($table, null, 'item_type');
            $order = $sql->setOrder($table, $order, 'item_type_sub');
            $inner_join = $sql->setInnerJoin('user', $table, 'user', 'user', 'id');
            $result = $sql->getSelectResult($table, $where, $order, null, $clume, null, $inner_join);

            foreach ($result as $item) {
                $item_preview = '';

                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($item["item_preview$i"])) {
                        $item_preview = $item["item_preview$i"];
                        break;
                    }
                }

                $item['item_preview'] = $item_preview;

                if ($item['id'] === $white_item_id) {
                    $white_items[$item['user']][$item['id']] = $item;
                } elseif (in_array($item['id'], $other_item_ids)) {
                    $other_items[$item['user']][$item['id']] = $item;
                } else {
                    $user_items[array_search($item['user'], $user_ids)][$item['id']] = $item;
                }
            }

            foreach ($user_ids as $key => $user_id) {
                if (!empty($white_items[$user_id])) {
                    $user_items[$key] = $white_items[$user_id] + $user_items[$key];
                }

                if (!empty($other_items[$user_id])) {
                    $user_items[$key] += $other_items[$user_id];
                }
            }

            ksort($user_items);

            foreach ($user_items as $an_user_items) {
                $items += $an_user_items;
            }

            Globals::setItems($items, 'lespros_items');
        }

        if (!empty($c[2])) {
            $user_ids = [$c[2]];
        }

        foreach ($items as $item) {
            if (in_array($item['user'], $user_ids)) {
                $items_html .= sprintf($item_html, $item['item_preview'],
                    $lazy_load, $item['name'], $item['name'], $item['id'],
                    sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf($tweet_text, $item['user_name']))));
            }
        }

        return $items_html;
    }

    static function draw_hide_all_item_by_user_url()
    {
        global $sql;
        if (!empty(Globals::get('email'))) {
            $user = $sql->keySelectRecord('user', 'mail', Globals::get('email'));

            if (!empty($user) && !empty($user['id'])) {
                return sprintf('◆ <a href="#" onclick="hiddenAllItemOfSelectedUser(\'%s\'); return false;">このユーザーのアイテムを全て非表示する</a><br />
    <br />', trim($user['id']));
            }
        }
    }

    static function draw_create_pasral_item($c, $data)
    {
        if (!empty(Globals::session('LOGIN_ID')) &&
            in_array($data['design_type'], ['new', 'edit']) &&
            (empty($data['direct_mode']) || !empty($data['direct_mode']) && $data['direct_mode'] != 'design_free')
        ) {
            global $device_path;

            $style = 'inline-block';

            if ($device_path != 'pc/') {
                $style = 'block';
            }

            return sprintf('<a style="display: %s" href="/proc.php?run=itemReg&cart_id=%s" class="btn_search add01"><span>販売する</span></a>', $style, $data['cart_id']);
        }

        return '';
    }

    /**
     * Get item price
     *
     * @param $c
     * @param $data
     * @return string
     */
    static function drawItemPrice($c, $data)
    {
        global $sql;

        if (empty($data['item_type']) && empty($data['item_type_sub'])
            && empty($data['item_preview1']) && empty($data['item_preview2'])
            && empty($data['item_preview3']) && empty($data['item_preview4'])) {

            $item = $sql->keySelectRecord("item", "id", $data['id']);
            $data['item_type'] = $item['item_type'];
            $data['item_type_sub'] = $item['item_type_sub'];
            for ($i = 1; $i <= 4; $i++) {
                $data['item_preview' . $i] = $item['item_preview' . $i];
            }
        }

        return getToolItemPrice($data, $data['item_type'], $data['item_type_sub']);
    }

    static function draw_ranking_tshirt_atjam($c)
    {
        global $sql;
        $table = 'item';
        $tmp = '';

        foreach (RANK_TSHIRT_ATJAM[$c[2]] as $key => $value) {
            $where = $sql->setWhere($table, null, 'user', '=', $value);
            $where = $sql->setWhere($table, $where, 'owner_item', '=', '');
            $where = $sql->setWhere($table, $where, 'item_type', '=', 'IT367');
            $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
            $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
            $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
            $where = $sql->setWhere($table, $where, 'owner', '=', '');
            $where = $sql->setWhere($table, $where, 'state', '=', 1);

            $clume = $sql->setClume('user', null, 'name');
            $clume = $sql->setClume($table, $clume, 'id');
            $innerJoin = $sql->setInnerJoin('user', $table, 'user', 'user', 'id');

            $result = $sql->getSelectResult($table, $where, '', '', $clume, '', $innerJoin);
            $group_name = '';
            while ($rec = $sql->sql_fetch_assoc($result)) {
                array_filter(USER_ATJAM, function ($user_ids, $group) use (&$group_name, $value) {
                    if (in_array($value, $user_ids)) {
                        $group_name = sprintf('<p>%s</p>', $group);
                    }
                }, ARRAY_FILTER_USE_BOTH);

                $tmp .= sprintf('<li>
                                        <a href="/market/%s">
                                            <div>%s
                                                %s
                                            </div>
                                        </a>
                        </li>', $rec['id'], $group_name, sprintf('<p>%s</p>', $rec['name']));
            }
        }

        return $tmp;
    }

    static function draw_item_campaign($c, $data)
    {
        global $sql;
        global $cc;
        $table = 'item';
        $tmp = '';

        $template = SystemUtil::getPartsTemplate($c[2], 'item');

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $clume = $sql->setClume($table, $clume, 'item_preview1');
        $clume = $sql->setClume($table, $clume, 'item_preview2');
        $clume = $sql->setClume($table, $clume, 'item_preview3');
        $clume = $sql->setClume($table, $clume, 'item_preview4');
        $clume = $sql->setClume($table, $clume, 'flag_preview_item');
        $clume = $sql->setClume($table, $clume, 'price');

        $where = $sql->setWhere($table, null, 'user', '=', $c[3]);
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
        $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
        $where = $sql->setWhere($table, $where, 'owner', '=', '');

        $order = $sql->setOrder($table, null, 'regist_unix', 'DESC');

        $result = $sql->getSelectResult($table, $where, $order, null, $clume);

        $items = [];
        $counter = count(constants::ORDER_NAMES) + 1;

        while ($rec = $sql->sql_fetch_assoc($result)) {

            if (in_array($rec['name'], constants::ORDER_NAMES)) {
                $items[array_search($rec['name'], constants::ORDER_NAMES)] = $rec;
            } else {
                $counter++;
                $items[$counter] = $rec;
            }
        }

        ksort($items);

        foreach ($items as $rec) {
            $rec['user_name'] = $data['name'];
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function draw_tweet_campaign($c, $data)
    {

        if (empty($c[3])) {
            $id = $data['id'];
        } else {
            $id = $data['item'];
        }

        if ($c[2] == 'heytaxi') {
            $text = 'TBSラジオ　月曜夜9時30分より放送中「かまいたちのヘイ！タクシー！」グッズがアップティーとコラボ販売中！
%s
 #かまタク　#かまいたち　#山内健司　#濱家隆一 #up_t #アップティー';
        } elseif ($c[2] == 'uuumfes') {
            $text = '#%s　と　Up-Tがコラボでグッズ販売！！
頂点を極めろ！！クリエーターコラボグッズFES開催！！ ツイートで無料でグッズも買える！
%s/uuumfes
#オリジナルTシャツ #オリジナルグッズ　#up_t  #アップティー　#クリエーターグッズフェス';

            return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf($text, str_replace(" ", "", $data['user_name']), ApiConfig::DOMAIN)));
        } else {
            $text = '吉本興業の住みます芸人、ぶんぶんボウルとアップティーがコラボ。ラジオ番組、ぶんぶんボウルの休み時間でカッコいいグッズから面白いグッズまで販売中！！
%s
 #ぶんぶんボウル #まーし #とよしげ #吉本芸人 #MRO #ぶんぶんボウルの休み時間 #アップティー #up_t ';
        }

        return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf($text, ApiConfig::DOMAIN . '/market/' . $id)));
    }

    static function rankingTweetCampaign($c, $data)
    {
        global $sql;
        $tmp = '';
        $number = 1;
        $table = 'twitter_ranks';

        $where = $sql->setWhere($table, null, 'pick_up', '=', '1');
        $where = $sql->setWhere($table, $where, 'tweet_type', '=', $c[2]);
        $order = $sql->setOrder($table, null, "regist_unix");
        $result = $sql->getSelectResult($table, $where, $order);

        if ($c[3] == 'check') {
            return $result->num_rows;
        }

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($c[2] != 5) {
                $tmp .= sprintf("<tr>
                                    <th>%s〜%sツイート当選者</th>
                                    <th>TwitterID：%s様</th>
                                </tr>", $number, $number + 99, sprintf('<a href="https://twitter.com/%s" target="_blank">%s</a>', $rec['screen_name'], $rec['name']));
            } else {

                if ($c[4] == 'sp') {
                    $tmp .= sprintf("<tr>
                                                <th>%s</th>
                                                <th>
                                                    <span>%s〜%sツイート：</span>
                                                    <span>当選者%s</span>
                                            </tr>", $rec['creator'], $number, $number + 99, sprintf('<a href="https://twitter.com/%s" target="_blank">%s</a>', $rec['screen_name'], $rec['name']));
                } else {
                    $tmp .= sprintf("<tr>
                                        <th>%s</th>
                                        <th>%s〜%s</th>
                                        <th>当選者%s</th></tr>", $rec['creator'], $number, $number + 99, sprintf('<a href="https://twitter.com/%s" target="_blank">%s</a>', $rec['screen_name'], $rec['name']));
                }
            }
            $number += 100;
        }

        return $tmp;
    }

    static function draw_hidden_category_id($c, $data)
    {
        $category_id = is_limit_items_by_user($data, true);

        if ($category_id) {
            return sprintf('<input type="hidden" id="category_id" value="%s">', $category_id);
        }
    }

    static function draw_mark_up_qa()
    {
        if ($_SERVER['SCRIPT_NAME'] == '/index.php') {
            return '<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [{
            "@type": "Question",
            "name": "初めてオリジナルTシャツを作成するのですが、個人用に1枚だけでも注文可能ですか？",
            "acceptedAnswer": {
                "@type": "Answer",
                "text": "はい、もちろん可能です。誰でも1枚から送料無料でオリジナルTシャツを作成することができます。"
            }
        }, {
            "@type": "Question",
            "name": "オリジナルTシャツは1枚いくら位で作成ができますか？ 何によって価格が変わりますか？",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "最も人気のある<a href=' . "'https://up-t.jp/item-detail/オリジナルTシャツ/半袖Tシャツ/IT001'" . '>定番Tシャツ</a>は表面1箇所プリントですと1枚2,400円(税込)です。Up-Tでは「ボディ料金+印刷料金」という料金形態になっていますので印刷面が増えると価格が変わるようになっています。一方で色数や印刷範囲の大小で価格は変わりません。5枚以上からは<a href=' . "'https://up-t.jp/page.php?p=matomete'" . '>「まとめて割」</a>が適用されてお安くお買い求めいただけます。<a href=' . "'https://up-t.jp/item-detail'" . '>価格一覧</a>はこちらから。"
            }}, {
            "@type": "Question",
            "name": "デザインセンスに自信がないのですが...。",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "まずは画像をアップロードするだけのデザインからスタートしてみてはいかがでしょうか？実は多くのお客様が画像アップロードのみのデザインでグッズ制作を行っています。/nUp-Tでは専用のデザインツールをご用意しています。最初は慣れるのに時間がかかるかもしれませんが、活用できれば一気にデザインの幅を広げてくれます。/nもし、デザインに不安がある場合は<a href=' . "'https://up-t.jp/designenq/'" . '>無料でデザイン制作を代行するサービス<a>を行っております。ぜひご活用ください。"
        }
      }]
    }
    </script>';
        }
    }

    static function draw_idol_jumping()
    {
        global $sql;
        global $cc;
        $table = 'user';
        $tmp = '';
        $template = SystemUtil::getPartsTemplate('jumping', 'idol');

        $where = $sql->setWhere($table, null, 'user_type', '=', 11);
        $order = $sql->setOrder($table, null, 'sort_by');
        $result = $sql->getSelectResult($table, $where, $order);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function draw_item_jumping()
    {
        global $sql;
        global $cc;
        $table = 'item';
        $tmp = '';
        $template = SystemUtil::getPartsTemplate('jumping', 'item');

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $clume = $sql->setClume($table, $clume, 'item_preview1');
        $clume = $sql->setClume($table, $clume, 'price');
        $clume = $sql->setClume($table, $clume, 'item_type');
        $clume = $sql->setClume('master_item_type', $clume, 'name', null, 'item_type_name');

        $inner_join = $sql->setInnerJoin('master_item_type', $table, 'item_type', 'master_item_type', 'id');

        $where = $sql->setWhere($table, null, 'user', '=', '6167e6f554a55');
        $where = $sql->setWhere($table, $where, 'state', '=', 1);
        $where = $sql->setWhere($table, $where, "buy_state", "=", 1);
        $where = $sql->setWhere($table, $where, "2nd_owner_state", "=", 1);
        $where = $sql->setWhere($table, $where, 'regist_unix', '>', 0);
        $where = $sql->setWhere($table, $where, 'owner', '=', '');

        $order = $sql->setOrder('item', null, 'regist_unix');

        $result = $sql->getSelectResult($table, $where, $order, null, $clume, '', $inner_join);
        while ($rec = $sql->sql_fetch_assoc($result)) {

            if (!empty($rec["flag_preview_item"]) && !empty($rec["item_preview" . $rec["flag_preview_item"]])) {

                $rec['item_preview'] = $rec["item_preview" . $rec["flag_preview_item"]];

            } else {
                for ($i = 1; $i <= 4; $i++) {
                    if (!empty($rec["item_preview" . $i])) {
                        $rec['item_preview'] = $rec["item_preview" . $i];
                        break;
                    }
                }
            }
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function draw_tweet_jumping($c, $data)
    {
        $text = '石川県の元気応援隊のジャンピン（Jumping)のグッズをUp-Tとコラボで販売！
みんなでジャンピンを応援しよう！！
%s
#ジャンピン #jumping #石川県 #アイドル #アップティー #up_t';

        return sprintf('https://twitter.com/intent/tweet?text=%s', urlencode(sprintf($text, ApiConfig::DOMAIN . '/market/' . $data['id'])));
    }

    static function drawHiddenInput($c)
    {
        return sprintf('<input type="hidden" name="%s" value="%s">', $c[2], Globals::get($c[2]));
    }

    static function draw_pc_style($c)
    {
        if (is_detail_page()) {
            $script = sprintf('<link rel="stylesheet" href="/common/css/pc/details-page-new-pc.css%s">', $c[2]);
        } else {
            $script = sprintf('<link rel="stylesheet" href="/common/css/libs/font-awesome.min.css"><link rel="stylesheet" href="/common/css/pc/nobody_base.min.css%s">', $c[2]);
        }

        return $script;
    }

    static function draw_campaign_user($c)
    {
        global $sql;
        global $cc;
        $table = 'user';
        $tmp = '';

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'profile_image');
        $clume = $sql->setClume($table, $clume, 'profile_text');
        $clume = $sql->setClume($table, $clume, 'name');
        $where = $sql->setWhere($table, null, 'user_type', '=', $c[3]);
        $order = $sql->setOrder($table, null, 'sort_by');

        $result = $sql->getSelectResult($table, $where, $order, null, $clume);

        $template = SystemUtil::getPartsTemplate($c[2], 'user');
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= $cc->run($template, $rec);
        }

        return $tmp;
    }

    static function draw_item_uuumfes($c)
    {
        global $sql;
        $tmp = '';

        $query = sprintf('SELECT * FROM item WHERE market_type = %s AND type_time > 0 AND regist_unix > 0 AND user != "54e4246c09e81" AND state = 1 AND user IN ((SELECT id FROM user WHERE state=1)) AND buy_state = 1 AND 2nd_owner_state = 1', UUUMFES_TYPE);

        if (!empty($c[2]) && $c[2] == 'original') {
            $query .= sprintf(' AND user IN ("%s")', implode('","', USER_CAMPAIGN[5]));
        }
        if (!empty($c[2]) && $c[2] == 'second') {
            $query .= sprintf(' AND user NOT IN ("%s")', implode('","', USER_CAMPAIGN[5]));
        }

        $result = $sql->rawQuery($query);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $item_preview = Extension::drawPreviewImage([2 => 'item_preview', 3 => '150', 4 => 'layzyest_check'], $rec);
            $tmp .= sprintf('<li><a href="/market/%s">%s</li></a>', $rec['id'], $item_preview);
        }

        return $tmp;
    }

    static function drawLinkToolCart($c, $data)
    {
        global $sql;
        if (isset($c[6]) && $c[6] == 'mypage') {
            $design_id = $c[3];
            $cart_id = !empty($c[4]) ? $c[4] : false;
            $user_id = $data['owner'];
            $item_id = $data['id'];
        } else {
            $design_id = $c[2];
            $cart_id = !empty($c[3]) ? $c[3] : false;
            $user_id = $data['user_id'];
            $item_id = $data['item_id'];
        }
        $tool_url = Extension::getDrawToolLink([3 => $design_id, 4 => $cart_id], []);
        if (empty($user_id)) {
            $item = $sql->selectRecord('item', $item_id);
            $user_id = $item['user'];
        }
        if (in_array($user_id, USER_YAMAN)) {
            $tool_url .= '&yaman=true';
        }
        return $tool_url;
    }

    static function draw_faq_category($c)
    {
        global $sql;
        $tmp = '';
        $i = 1;

        $table = 'faq_group';
        $where = $sql->setWhere($table, null, "state", "=", 1);
        $order = $sql->setOrder($table, null, "order_id", "ASC");
        $faq_category = $sql->getSelectResult($table, $where, $order);
        if (!empty($c[2]) && $c[2] === 'pc') {
            while ($rec = $sql->sql_fetch_assoc($faq_category)) {
                $tmp .= '<div class="col-3 mb-3">
					<a class="faq-page-item-t" href="#faq-cate-' . $i . '">' . $rec['name'] . '</a>
            </div>';
                $i++;
            }
        } else {
            while ($rec = $sql->sql_fetch_assoc($faq_category)) {
                $tmp .= '<div class="col-6 col-md-3 mb-3">
					<a class="faq-page-item-t" href="#faq-cate-' . $i . '">' . $rec['name'] . '</a>
            </div>';
                $i++;
            }
        }

        return $tmp;
    }

    static function draw_faq_content()
    {
        global $sql;
        $tmp = '';
        $i = 1;
        $j = 1;

        $table = 'faq_group';
        $where = $sql->setWhere($table, null, "state", "=", 1);
        $order = $sql->setOrder($table, null, "order_id", "ASC");
        $faq_category = $sql->getSelectResult($table, $where, $order);
        while ($rec = $sql->sql_fetch_assoc($faq_category)) {
            $tmp .= '<div class="faq-item-cate" id="faq-cate-' . $i . '">
				  <h3 class="faq-cate-title">' . $rec['name'] . '</h3>
				  <div id="qa">
					  <div class="inner">
						  <div class="conBox">
							  <div id="faqArea">';
            $table = "faq";
            $where = $sql->setWhere($table, null, "state", "=", 1);
            $where = $sql->setWhere($table, $where, "group_id", "=", $rec['id']);
            $order = $sql->setOrder($table, null, "order_id", "ASC");
            $result = $sql->getSelectResult($table, $where, $order);
            while ($content = $sql->sql_fetch_assoc($result)) {
                $tmp .= '<div class="cp_actab">
									  <input id="tab-3' . $j . '" type="checkbox" name="tabs">
									  <label for="tab-3' . $j . '">
									   <div class="qaTextBox">
											  <p class="qaCircle qBg"><span>Q</span></p>
											  <p class="qaText qaTextSp">' . $content['question'] . '</p>
										  </div>
									  </label>
									  <div class="cp_actab-content">
										  <div class="qaTextBox">
											  <p class="qaCircle aBg"><span class="ans">A</span></p>
											  <p class="aText qaTextSp">' . $content['answer'] . '</p>
										  </div>
									  </div>
								  </div>';
                $j++;
            }
            $tmp .= '</div>
						  </div>
					  </div>
				  </div>
			  </div>';
            $i++;
        }

        return $tmp;
    }

    public function drawCategoryCbox($c)
    {
        global $sql;
        $tmp = '';

        $table = $c[3];
        if ($c[4] == 'item-detail'){
            $where = $sql->setWhere($table, null, "is_deleted", "=", 0);
            $order = $sql->setOrder($table, null, 'web_order', 'ASC');
        } else if ($c[4] == 'news') {
            $where = $sql->setWhere($table, null, "state", "=", 1);
            $order = $sql->setOrder($table, null, 'sort', 'ASC');
        }
        $result = $sql->getSelectResult($table, $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($c[4] == 'item-detail') {
                if ($rec['id'] == 11) {
                    $rec['title'] = 'その他';
                }
                if ($c[2] == 'pc') {
                    $tmp .= '<div class="item" data-value="' . $rec['id'] . '">' . $rec['title'] . '</div>';
                } elseif ($c[2] == 'sp') {
                    $tmp .= '<option value="' . $rec['id'] . '">' . $rec['title'] . '</option>';
                }
            } else if ($c[4] == 'news') {
                $tmp .= '<div class="item" data-value="' . $rec['id'] . '">' . $rec['name'] . '</div>';
            }
        }

        return $tmp;
    }

    public function drawItemPageFAQ()
    {
        global $sql;
        $tmp = '';
        $i = 1;

        $where = $sql->setWhere('faq', null, 'item_page_state', '=', '1');
        $result = $sql->getSelectResult('faq', $where);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= '<div class="cp_actab">
								<input id="tab-' . $i . '" type="checkbox" name="tabs">
								<label for="tab-' . $i . '">
									<div class="qaTextBox">
										<p class="qaCircle qBg"><span>Q</span></p>
										<p class="qaText qaTextSp">' . $rec['question'] . '</p>
									</div>
								</label>
								<div class="cp_actab-content">
									<div class="qaTextBox">
										<p class="qaCircle aBg"><span class="ans">A</span></p>
										<p class="aText qaTextSp">' . $rec['answer'] . '</p>
									</div>
								</div>
							</div>';
            $i++;
        }

        return $tmp;
    }

    static function drawSaleStatus($c)
    {
        global $sql;
        $tmp = '';
        $status = [
            0 => 'NORMAL',
            1 => 'SALE',
            2 => 'NEW'
        ];

        $result = $sql->selectRecord('master_item_type', $c[2]);
        for ($i = 0; $i < 3; $i++) {
            if ($result['sales_status'] == $i) {
                $tmp .= '<label><input type="radio" name="sales_status" value="' . $result['sales_status'] . '" checked="checked" />' . $status[$i] . '</label>' . "\n";
            } else {
                $tmp .= '<label><input type="radio" name="sales_status" value="' . $i . '" />' . $status[$i] . '</label>' . "\n";
            }
        }

        return $tmp;
    }

    public function drawNews()
    {
        global $sql;
        $tmp = '';
        $table = 'information';

        $where = $sql->setWhere($table, '', 'state', '=', '1');
        if (Globals::session('LOGIN_TYPE') == 'user') {
            $where = $sql->setWhere($table, $where, 'user_type', 'LIKE', '%user%');
        }
        $order = $sql->setOrder($table, '', 'update_unix', 'DESC');
        $results = $sql->getSelectResult($table, $where, $order, 3);

        while ($result = $sql->sql_fetch_assoc($results)) {
            if ($result['url'] == '') {
                $tmp .= '<p><span>' . $result['update_y'] . '.' . $result['update_m'] . '.' . $result['update_d'] . '</span><span class="btn"><a style="color: #626262; cursor: text">' . $result['title'] . '</a></span></p>';
            } else {
                $tmp .= '<p><span>' . $result['update_y'] . '.' . $result['update_m'] . '.' . $result['update_d'] . '</span><span class="btn"><a href="' . $result['url'] . '" target="' . $result['target_type'] . '">' . $result['title'] . '</a></span></p>';
            }
        }
        return $tmp;
    }

    public function categoryItemListPage($c)
    {
        global $cc;

        list($count, $tmp) = getMasterCategory(10, 0);

        if ($count > 10) {
            $cc->setVariable("category_more", 1);
        }

        return $tmp;
    }

    static function bannerAdmin()
    {
        global $sql;
        $tmp = '';
        $where = null;

        if (Globals::session('LOGIN_TYPE') != 'admin') {
            $where = $sql->setWhere("banner", $where, "is_actual", "=", 1);
        }
        $order = $sql->setOrder("banner", null, "position", "ASC");
        $result = $sql->getSelectResult("banner", $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $active = '';
            $noActive = '';
            if($rec['is_actual'] == 1) {
                $active = 'checked';
            }else {
                $noActive = 'checked';
            }

            if (Globals::session('LOGIN_TYPE') == 'admin') {
                $tmp .= '<div class="table mb_30">
                <input type="hidden" name="id" value="' . $rec['id'] . '">
                <table width="728" border="0" cellspacing="0" cellpadding="0">
                    <tr class="even">
                    <td class="item">
                       <label>タイトル</label>
                        <input type="text" name="title" value="' . $rec['title'] . '">
                    </td>
                    <td class="form" rowspan="6">
                        <img class="banner" src="' . $rec['image_url'] . '">
                    </td>
                </tr>
                <tr class="even">
                    <td class="item">
                        <label>コメント</label>
                        <input type="text" name="comment" value="' . $rec['comment'] . '">
                    </td>
                </tr>
                <tr class="even">
                   <td class="item">
                        <label>URL</label>
                        <input type="text" name="url" value="' . $rec['url'] . '">
                    </td>
                </tr>
                <tr class="even">
                   <td class="item">
                        <label>掲載順位</label>
                        <input type="text" name="position" value="' . $rec['position'] . '">
                    </td>
                </tr>
                <tr class="even">
                   <td class="item">
                        <label>状態</label>
                        <label><input type="radio" name="is_actual_' . $rec['id'] . '" value="2"' . $noActive . '>非表示</label>
                        <label><input type="radio" name="is_actual_' . $rec['id'] . '" value="1"' . $active . '>表示</label>
                    </td>
                </tr>
                <tr class="even">
                  <td class="item"> 
                  <a href="#" class="update-banner" style="margin-right: 15px">更新する</a>
                  <a href="#" class="delete-banner" style="color: red">削除する</a><br/>
                  </td>
                </tr>
                </table>
                </div>';
            } else {
                $tmp .= '<div>
					<div class="img-banner">
						<div style="position: relative">
						<a href="' . $rec['url'] . '"><img data-src="' . $rec['image_url'] . '" alt="image" class="lazyload img-slider" ></a>
						<span class="banner-title">' . $rec['title'] . '</span>
						<span class="banner-comment">' . $rec['comment'] . '</span>
						</div>
					</div>
				</div>';
            }
        }

        return $tmp;
    }

    public function topPageLineUp($c)
    {
        global $sql;
        $ranks = ['no_1', 'no_2', 'no_3'];
        $itemRank = [];
        $tmp = '';

        $table = 'master_item_type_page';
        $where = $sql->setWhere($table, null, 'top_page', '=', 1);
        $order = $sql->setOrder($table, null, 'order', 'ASC');
        $clume = $sql->setClume($table, null, 'item_text_detail');
        $clume = $sql->setClume($table, $clume, 'size_template');
        $clume = $sql->setClume($table, $clume, 'preview_image');
        $clume = $sql->setClume($table, $clume, 'preview_image2');
        $clume = $sql->setClume($table, $clume, 'preview_image2');
        $clume = $sql->setClume($table, $clume, 'top_page_size');
        $clume = $sql->setClume($table, $clume, 'print_method_id');
        $clume = $sql->setClume('master_item_type', $clume, 'id');
        $clume = $sql->setClume('master_item_type', $clume, 'sales_status');
        $clume = $sql->setClume('master_item_type', $clume, 'order_suspended');
        $clume = $sql->setClume('master_item_type', $clume, 'is_discount');
        $clume = $sql->setClume('master_item_type', $clume, 'discount_coupon');
        $clume = $sql->setClume('master_item_type', $clume, 'name');
        $clume = $sql->setClume('master_item_type', $clume, 'tool_price');
        $clume = $sql->setClume('master_item_type', $clume, 'sale_price');
        $clume = $sql->setClume('master_item_type', $clume, 'item_price');
        $clume = $sql->setClume('master_item_type', $clume, 'material');
        $where = $sql->setWhere('master_item_type', $where, 'state', '=', 1);
        $innerJoin = $sql->setInnerJoin('master_item_type', 'master_item_type', 'id', $table, 'item_type');
        $rows = $sql->getSelectResult($table, $where, $order, null, $clume, null, $innerJoin);

        $items = $sql->rawQuery('SELECT * FROM item_ranking');
        while ($rec = $sql->sql_fetch_assoc($items)) {
            foreach ($ranks as $rank) {
                $itemRank[] = $rec[$rank];
            }
        }
        while ($item = $sql->sql_fetch_assoc($rows)) {
            if (in_array($item['id'], $itemRank)){
                $ranking = '<div class="crown"><img src="/common/img/toppage/ic-crown-yellow.png" alt="icon"></div>';
            }else {
                $ranking = '';
            }
            if ($item['tool_price'] != 0) {
                $item['price'] = number_format($item['tool_price'] * 1.1, 0, ".", ",");
            } else {
                $item['price'] = number_format($item['item_price'] * 1.1, 0, ".", ",");
            }

            if (mb_substr($item['item_text_detail'], 100) != '') {
                $item['item_text_detail'] = mb_substr($item['item_text_detail'], 0, 99);
                $item['item_text_detail'] .= '...';
            }

            if ($item['order_suspended'] == 1) {
                $item['sale_status'] = '<span class="sale_tag order-suspend">注文停止中</span>';
            }else {
                if ($item['sales_status'] == 1) {
                    $item['sale_status'] = '<span class="sale_tag">SALE</span>';
                } else if ($item['sales_status'] == 2) {
                    $item['sale_status'] = '<span class="sale_tag new_tag">NEW</span>';
                } else {
                    $item['sale_status'] = '';
                }
            }
            if($item['is_discount'] == 1) {
                $is_discount = '<p class="is_discount_btn">クーポンプレゼント</p>';
            }else {
                $is_discount = '';
            }
            if ($c[2] == 'pc') {
                $tmp .= '<div class="item">
                        <div style="display: flex; justify-content: center">
                            <a href="/item-detail/' . $item['id'] . '" target="_blank" class="img_link slide-effect-thumnail">
                            <div style="position: relative">
                                <img class="lazyload" data-src="' . $item['preview_image'] . '" alt="product thumnail">
                                <img class="lazyload" data-src="' . $item['preview_image2'] . '" alt="product thumnail">                                
                                ' . $ranking . $item['sale_status'] . '
                            </div>
                            </a>
                        </div>
                        <div class="text_box">
                            <p class="title"><a href="/item-detail/' . $item['id'] . '">' . $item['name'] . '</a></p>
                            <p class="stitle">' . $item['material'] . '</p>
                            <p class="title">1枚 ' . $item['price'] . '円（税込）</p>
                            ' . $is_discount . '
                            <p class="txt">' . $item['item_text_detail'] . '</p>             
                            <p class="size">' . $item['top_page_size'] . '</p>
                            <a href="/item-detail/' . $item['id'] . '" target="_blank" class="button">詳しくはこちら</a>
                        </div>
                    </div>';
            } else {
                $tmp .= '<div class="item">
                        <div>
                            <a href="/item-detail/' . $item['id'] . '" target="_blank" class="img_link slide-effect-thumnail">
                            <div style="position: relative">
                                <img class="lazyload" data-src="' . $item['preview_image'] . '" alt="product thumnail">
                                <img class="lazyload" data-src="' . $item['preview_image2'] . '" alt="product thumnail">
                                ' . $ranking . $item['sale_status'] . '
                            </div>
                            </a>
                        </div>
                        <div class="text_box">
                            <p class="title"><a href="/item-detail/' . $item['id'] . '">' . $item['name'] . '</a></p>
                            <p class="stitle">' . $item['material'] . '</p>
                            <p class="title">1枚 ' . $item['price'] . '円（税込）</p>
                            ' . $is_discount . '
                            <p class="txt">' . $item['item_text_detail'] . '</p>             
                            <p class="size">' . $item['top_page_size'] . '</p>
                            <a href="/item-detail/' . $item['id'] . '" target="_blank" class="button">詳しくはこちら</a>
                        </div>
                    </div>';
            }

        }

        return $tmp;
    }

    public function topPageFAQ()
    {
        global $sql;

        $table = 'faq';
        $tmp = '';
        $i = 31;
        $where = $sql->setWhere($table, null, 'item_page_state', '=', 1);
        $order = $sql->setOrder($table, null, 'order_id', 'ASC');
        $rows = $sql->getSelectResult($table, $where, $order);

        while ($item = $sql->sql_fetch_assoc($rows)) {
            $tmp .= '<div class="cp_actab">
                        <input id="tab-' . $i . '" type="checkbox" name="tabs">
                        <label for="tab-' . $i . '">
                            <div class="qaTextBox">
                                <p class="qaCircle qBg"><span>Q</span></p>
                                <p class="qaText qaTextSp">' . $item['question'] . '</p>
                            </div>
                        </label>
                        <div class="cp_actab-content">
                            <div class="qaTextBox">
                                <p class="qaCircle aBg"><span class="ans">A</span></p>
                                <p class="aText qaTextSp">' . $item['answer'] . '</p>
                            </div>
                        </div>
                    </div>';

            $i++;
        }

        return $tmp;
    }

    public function columnList($c){
        global $sql;
        global $cc;
        $tmp ='<div class="list-news-n d-flex-ct flex-wrap">';
        $i = 1;
        $template = SystemUtil::getPartsTemplate('post_new', 'list');

        $table = 'post_new';
        $clume = $sql->setClume($table,null,'title');
        $clume = $sql->setClume($table,$clume,'id');
        $clume = $sql->setClume($table,$clume,'profile_image');
        $clume = $sql->setClume('post_new_category',$clume,'name');
        $where = $sql->setWhere($table,null,'public','=',1);
        $where = $sql->setWhere('post_new_category',$where,'state','=',1);
        if (!empty(Globals::get('name'))){
            $where = $sql->setWhere('post_new_category',$where,'name','=',Globals::get('name'));
        }
        $innerJoin = $sql->setInnerJoin('post_new_category','post_new_category','id',$table,'post_category_id');
        $result = $sql->getSelectResult($table,$where,null,null,$clume,null,$innerJoin);

        if (empty($result)){
            $tmp .= "<h3>該当する記事がありません。</h3></div>";
        } else {
            while ($new = $sql->sql_fetch_assoc($result)) {
                $tmp .= $cc->run($template, $new);

                if ($i == $c[2]) {
                    break;
                }
                $i++;
            }

            if ($i < $result->num_rows) {
                $tmp .= '</div>
                    <div style="text-align: center"><img src="common/img/load.gif" class="loading" style="display: none"></div>   
                    <div class="btn-load-more text-center btn-black-radius dpt-50">
                    <a class="load-more more-content">もっと見る</a>';
            } else {
                $tmp .= '</div>';
            }
        }

        return $tmp;
    }

    public function getTitlePost($c){
        global $sql;
        $tmp = '';

        $post = $sql->selectRecord("post_new",$c[2]);
        $text = explode("<h3",$post['content']);

        for ($i = 1; $i < count($text); $i++){
            $title = substr($text[$i],strpos($text[$i], "\">") + 2,strpos($text[$i],"</h3>"));
            $title = substr($title,0,strpos($title,"</h3>"));
            if($title != '') {
                $tmp .= '<li>
                                <a href="#title' . $i . '">' . $title . '</a>
                            </li>';
            }
        }

        return $tmp;
    }

    public function selectPostCategory($c){
        global $sql;
        $tmp = '';

        $result = $sql->rawQuery("SELECT `id`, `name` FROM `post_new_category` WHERE `state` = 1 ORDER BY `sort`");
        while ($category = $sql->sql_fetch_assoc($result)){
            if (!empty($c[2] && $category['id'] == $c[2])){
                $tmp .= '<option value="'.$category['id'].'" selected>'.$category['name'].'</option>';
            } else {
                $tmp .= '<option value="'.$category['id'].'">'.$category['name'].'</option>';
            }
        }

        return $tmp;
    }

    public function breakNewContent($c){
        global $sql;
        $tmp = '';
        $j = 1;

        $new = $sql->selectRecord('post_new',$c[2]);
        $text = explode("\n",$new['content']);
        for ($i = 1; $i <= count($text); ){
            $draft = substr($text[$i],strpos($text[$i],'>')+1,strpos($text[$i],"</h3>"));
            $draft = substr($draft,0,strpos($draft,"</h3>"));
            $tmp .= '<tr class="header">
                    <th style="vertical-align: middle;">タイトル'.$j.'</th>
                    <td></td>
                    <td><input style="width: 100%" type="text" name="header'.$j.'" value="'. $draft .'">';
            if ($j > 1){
                $tmp .= '<span class="delete" id="delete'.$j.'">X</span>';
            }
            $tmp .= '</td></tr>';
            $i = $i + 3;
            $draft = substr($text[$i],strpos($text[$i],"src=\"") + 5,strpos($text[$i],"\" alt"));
            $draft = substr($draft,0,strpos($draft,"\" alt"));
            $tmp .= '<tr class="profile_image">
                    <th style="vertical-align: middle;">画像'.$j.'</th>
                    <td></td>
                    <td><input style="width: 100%" type="text" name="profile_image'.$j.'" value="'. $draft .'"></td>
                       </tr>';
            $i = $i + 2;
            $draft = substr($text[$i],strpos($text[$i],"<p>") + 3, strpos($text[$i],"</p>"));
            $draft = substr($draft,0,strpos($draft,"</p>"));
            $draft = str_replace("</br>","\n",$draft);
            $tmp .= '<tr class="content">
                    <th style="vertical-align: middle;">内容'.$j.'</th>
                    <td></td>
                    <td><textarea rows="10" cols="80" name="new_content'.$j.'">'. $draft .'</textarea></td>
                       </tr>';
            if ($i == count($text) - 7){
                $i = $i + 4;
                $draft = substr($text[$i],strpos($text[$i],"href=\"") + 6, strpos($text[$i],"</a>"));
                $draft = substr($draft,0,strpos($draft,"\">"));
                $tmp .= '<tr class="new_button">
                        <th style="vertical-align: middle;">ボタン</th>
                        <td></td>
                        <td>リンク：<input type="text" name="link" value="'.$draft.'">';
                $draft = substr($text[$i],strpos($text[$i],"\">") + 2, strpos($text[$i],"</a>"));
                $draft = substr($draft,0,strpos($draft,"</a>"));
                $tmp .= 'ボタン名：<input type="text" name="button" value="'.$draft.'"></td></tr>';
                break;
            }
            $i = $i + 4;
            if($i > count($text)){
                $tmp .= '<tr class="new_button">
                        <th style="vertical-align: middle;">ボタン</th>
                        <td></td>
                        <td>リンク：<input type="text" name="link" value="">
                        ボタン名：<input type="text" name="button" value=""></td></tr>';
            }
            $j++;
        }

        return $tmp;
    }

    public function mailMagazineReceiver($c){
        global $sql;
        $tmp = '<div style="display: flex; justify-content: space-between; flex-wrap: wrap"> ';
        $mail = $sql->selectRecord('mail_template_magazine_store',$c[2]);
        $date = date_create($mail['send_date']);
        $table = 'email_queues';
        $where = $sql->setWhere($table,null,'subject','=',$mail['subject']);
        $where = $sql->setWhere($table,$where,'sent','=',1);
        $where = $sql->setWhere($table,$where,'send_at','>=',$date);
        $where = $sql->setWhere($table,$where,'send_at','<',$date->modify('+1 day')->format('Y-m-d 00:00:00'));
        $results = $sql->getSelectResult($table,$where);

        while ($item = $sql->sql_fetch_assoc($results)){
            $tmp .= '<p>'.$item['to'].'</p>';
        }
        $tmp .= '</div>';

        return $tmp;
    }

    public function topPageNewList(){
        global $sql;
        $table = 'post_new';
        $tmp = '';

        $clume = $sql->setClume($table,null,'title');
        $clume = $sql->setClume($table,$clume,'id');
        $clume = $sql->setClume($table,$clume,'profile_image');
        $clume = $sql->setClume('post_new_category',$clume,'name');
        $where = $sql->setWhere($table,null,'public','=',1);
        $where = $sql->setWhere('post_new_category',$where,'state','=',1);
        $order = $sql->setOrder($table,null,'created_at','DESC');
        $innerJoin = $sql->setInnerJoin('post_new_category','post_new_category','id',$table,'post_category_id');
        $result = $sql->getSelectResult($table,$where,$order,6,$clume,null,$innerJoin);

        while ($new = $sql->sql_fetch_assoc($result)){
            $tmp .= '<div class="item-list-news-n">
					<a href="/news/'.$new['id'].'/'.$new['title'].'">
						<div class="thumbnail">
							<img class="lazyload" data-src="'.$new['profile_image'].'" alt="thumbnails">
							<span class="type_tag">'.$new['name'].'</span>
						</div>
						<div class="description">
							<h3>'.$new['title'].'</h3>
						</div>
					</a>
				</div>';
        }

        return $tmp;
    }

    static function drawSelectMasterItem($c)
    {
        global $sql;
        $table = 'master_item_type';

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $where = $sql->setWhere($table,null,'category_id','IN',sprintf('SELECT id from master_categories where master_category_style_id ="%s"', $c[5]));
        $where = $sql->setWhere($table,$where,'state','=',1);
        $result = $sql->getSelectResult($table, $where, null, null, $clume);

        $tmp = '<select name="' . $c[3] . '">';
        $tmp .= '<option value="">全てのアイテム</option>';
        while ($rec = $sql->sql_fetch_assoc($result)) {
            if ($c[4] == $rec['id']) $tmp .= '<option value="' . $rec['id'] . '" selected>' . $rec['name'] . '</option>';
            else $tmp .= '<option value="' . $rec['id'] . '">' . $rec['name'] . '</option>';
        }
        $tmp .= '</select>';
        return $tmp;
    }

    static function drawListItemRanking()
    {
        global $cc;
        $items = getItemRanking(2);
        $tmp = '';
        $template = SystemUtil::getPartsTemplate('master_item_type', 'ranking');
        foreach ($items as $key => $item) {
            $item['item_ranking'] = $key;
            $tmp .= $cc->run($template, $item);
        }
        return $tmp;
    }

    static function drawOrderSuspend($c, $data) {
        global $sql;
        $tmp = '';
        $table = 'master_item_type';
        $clume = $sql->setClume($table, null, 'order_suspended');
        $where = $sql->setWhere($table,null,'id','=',$c[2]);
        $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, null, $clume));
        if ($result['order_suspended'] == 1) {
            $tmp .= '<label><input type="checkbox" name="order_suspended[]" value="1" checked>注文中止</label>
                    <input type="hidden" name="order_suspended_CHECK" value="true">';
        }else {
            $tmp .= '<label><input type="checkbox" name="order_suspended[]" value="1">注文中止</label>
                    <input type="hidden" name="order_suspended_CHECK" value="true">';
        }
        return $tmp;
    }

    static function drawDiscountCoupon($c, $data) {
        global $sql;
        $tmp = '';
        $table = 'master_item_type';
        $clume = $sql->setClume($table, null, 'is_discount');
        $clume = $sql->setClume($table, $clume, 'discount_coupon');
        $where = $sql->setWhere($table,null,'id','=',$c[2]);
        $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, null, $clume));
        if ($result['is_discount'] == 1) {
            $tmp .= '<label><input type="checkbox" name="is_discount[]" value="1" checked></label>
                    <input type="hidden" name="is_discount_CHECK" value="true">
                    <input type="text" name="discount_coupon" value="' . $result['discount_coupon'] . '" size="30" maxlength="255">';
        }else {
            $tmp .= '<label><input type="checkbox" name="is_discount[]" value="1"></label>
                    <input type="hidden" name="is_discount_CHECK" value="true">
                    <input type="text" name="discount_coupon" value="' . $result['discount_coupon'] . '" size="30" maxlength="255">';
        }
        return $tmp;
    }

    static function drawPrintMethod($c) {
        global $sql;
        $itemType = $c[2];

        $clume = $sql->setClume('master_item_type_page', null, 'print_method_id');
        $where = $sql->setWhere('master_item_type_page',null,'item_type','=',$itemType);
        $itemTypePage = $sql->sql_fetch_assoc($sql->getSelectResult('master_item_type_page', $where, null, null, $clume));

        $printMethod = $sql->selectRecord('print_method', $itemTypePage['print_method_id']);

        return $printMethod['title'];
    }

    static function draw_select_item_type()
    {
        global $sql;
        $table = 'master_item_type';
        $tmp = '<select name="item_type" id="item_select"><option value="" data-lookup="" selected="">選択してください</option>';

        $clume = $sql->setClume($table, null, 'id');
        $clume = $sql->setClume($table, $clume, 'name');
        $result = $sql->getSelectResult($table, null, null, null, $clume);
        while ($rec = $sql->sql_fetch_assoc($result)) {
            $tmp .= sprintf('<option value="%s" data-lookup="%s">%s</option>', $rec['id'], $rec['id'], $rec['name']);
        }

        $tmp .= '</select>';

        return $tmp;
    }
}
