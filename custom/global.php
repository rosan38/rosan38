<?php
	require_once 'vendor/autoload.php';
	require_once 'config/ApiConfig.php';

use Knp\Snappy\Pdf;
//SEOに関するテキスト分岐
	const TYPE_PRINT = 'p';
	const LAZE_TYPE = 'l';
	const PRINT_LAZE = 'pl';
	const PRINT_EMBROIDERY = 'pe';
	const EMBROIDERY = 'e';
	const RINGPASRAL = 'pasral';
	const ORIGINAL_ID = 'IT370';
	const ORIGINAL_SIZES = ['ITSI5396','ITSI5397','ITSI5398'];
	const CLONE_SIZES = ['ITSI7293', 'ITSI7294', 'ITSI7295'];
    // print_by_layers_item_price
    const EMBROIDERY_PRICE = [
        1 => 500,
        2 => 500,
        3 => 500,
        4 => 500,
    ];

	const LINE_CONFIG = [
        'client_id' => '1656951160',
        'redirect_uri' => ApiConfig::DOMAIN . 'proc.php',
        'client_secret' => '0d4d05a5babb91e429a3cd43f43c5b83',
	];
	const INSTA_CONFIG = [
        'client_id' => '496883438693553',
        'redirect_uri' => ApiConfig::DOMAIN . 'proc.php',
        'client_secret' => '00c7759b9fca867e40c9027ab9e5d54c',
	];

	const AMAZON_CONFIG = array
	(
		'merchant_id' => 'AC0ND72N3R7GI',
		'access_key'  => 'AKIAJOBTTNVFCCLN7NEQ',
		'secret_key'  => '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',
		'client_id'   => 'amzn1.application-oa2-client.96328af648a14ee984ff2529df2906b2',
		'region'      => 'jp',
		'sandbox'     => false
	);

	const UPOINT_STATE = [
	    'pending'   => 0,
        'available' => 1,
        'used'      => 2,
	    'canceled'  => 3,
	    'returned'  => 4,
	    'expiry'    => 5,
	    'title'     => [
	        0 => 'ポイント獲得(仮)',
	        1 => 'ポイント獲得',
            2 => 'ポイント利用',
            3 => '購入キャンセル',
            4 => 'ポイント返却',
            5 => '有効期限切れ',
	    ],
	];

	const PAY_STATE = [
	    'cancel' => 0,
	    'accept' => 1,
	];

	const BASE_CONFIG = array
	(
		'client_id' => 'c1c2cfeac16dd36912cc74ac59f69eb2',
		'client_secret'  => '64990a451d874edddce0296ff840fb4b',
		'redirect_uri'  => 'http://up-t.jp'
	);

	const CREATORS = [
		'YOUTUBER'             => 1,
		'ENTERTAINER'          => 2,
        '605ae6b104744'        => 6,
		'ENTERTAINER_WHO_LIVE' => 3,
	];

	const CREATOR_ID = [
        'maruiyoshimoto+kamaitachi@gmail.com' => '605ae6b104744',
        'maruiyoshimoto+ainsyutain@gmail.com' => '605ae6b162b5c',
        'maruiyoshimoto+newyork@gmail.com' => '605ae6b1f17dc',
        'maruiyoshimoto+suehirogarizu@gmail.com' => '605ae6b26e08b',
        'maruiyoshimoto+Runny Nose@gmail.com' => '605ae6b2e5adf',
        'maruiyoshimoto+elegant jinsei@gmail.com' => '605ae6b432499',
        'maruiyoshimoto+kuki kaidan@gmail.com' => '605ae6b491646',
        'maruiyoshimoto+ozwald@gmail.com' => '605ae6b503385',
        'maruiyoshimoto+univers@gmail.com' => '605ae6b578298',
        'maruiyoshimoto+laugh lecrin@gmail.com' => '605ae6b5ecf3c',
        'maruiyoshimoto+kaerutei@gmail.com' => '605ae6b669c6d',
        'maruiyoshimoto+rainbow@gmail.com' => '605ae6b70add0',
        'maruiyoshimoto+daitaku@gmail.com' => '605ae6b768e6a',
        'maruiyoshimoto+ultra boogies@gmail.com' => '605ae6b7c7ee5',
        'maruiyoshimoto+yasashiizu@gmail.com' => '605ae6b83162b',
        'maruiyoshimoto+nelsons@gmail.com' => '605ae6b89c00e',
        'maruiyoshimoto+zazy@gmail.com' => '605ae6b94170f',
        'maruiyoshimoto+team banana@gmail.com' => '605ae6ba427d6',
        'maruiyoshimoto+spike@gmail.com' => '605ae6babe228',
        'maruiyoshimoto+iron head@gmail.com' => '605ae6bb3e382',
        'maruiyoshimoto+daisizen@gmail.com' => '605ae6bbafd5a',
        'maruiyoshimoto+dansei blanco@gmail.com' => '605ae6bc52fa2',
        'maruiyoshimoto+soitudoitu@gmail.com' => '605ae6bcb1e30',
        'maruiyoshimoto+sunshine@gmail.com' => '605ae6bd1be40',
        'maruiyoshimoto+danviramoocho@gmail.com' => '605ae6bdd53ee',
        'maruiyoshimoto+diamond@gmail.com' => '605ae6be773a3',
        'maruiyoshimoto+ojyo@gmail.com' => '605ae6bed4f67',
        'maruiyoshimoto+bakukome@gmail.com' => '605ae6bf3fa4c',
        'maruiyoshimoto+ankanminkan@gmail.com' => '605ae6bff3449',
        'maruiyoshimoto+yuttarikan@gmail.com' => '605ae6c09ac5d',
        'maruiyoshimoto+chikakohonma@gmail.com' => '605ae6c105eb8',
        'maruiyoshimoto+bunbunbowl@gmail.com' => '605ae6c1651e9',
        'maruiyoshimoto+family restaurant@gmail.com' => '605ae6c21bebb',
        'maruiyoshimoto+hello@gmail.com' => '605ae6c2b68b0',
        'maruiyoshimoto+yamadanaoki@gmail.com' => '605ae6c321a5e',
        'maruiyoshimoto+arinkurin@gmail.com' => '605ae6c38880b',
        'maruiyoshimoto+penguinnuts@gmail.com' => '605ae6c38880c',
	];

    const PLUS_CREATOR_RANK = [
        '605ae6b104744' => ['60744fc8820e9' => 11, '60758c4ade498' => 4],
        '605ae6b162b5c' => ['60742d3a40420' => 3, '60742eeb44548' => 2],
        '605ae6b1f17dc' => ['6075958a28be2' => 14, '60743483496d5' => 3],
        '605ae6b26e08b' => ['60743a29a5a3c' => 1, '60743c84ae2e9' => 1],
        '605ae6b2e5adf' => ['6074490cacaa6' => 1],
        '605ae6b491646' => ['6074420b4c2f1' => 3, '6074471c49af3' => 1],
        '605ae6b503385' => ['607448791209d' => 2],
        '605ae6b578298' => ['6074529231f28' => 3, '6074537622f60' => 1],
        '605ae6b5ecf3c' => ['60744db6bc0f7' => 2],
        '605ae6b669c6d' => ['60745145766dd' => 2],
        '605ae6b70add0' => ['6072fcbf3d520' => 1, '60743de8dfb08' => 2],
        '605ae6b94170f' => ['60745d9b59c7f' => 2],
        '605ae6bc52fa2' => ['60746933cd2c3' => 1],
        '605ae6bcb1e30' => ['60746c419fc3b' => 1],
        '605ae6be773a3' => ['6074733b6a26a' => 1],
        '605ae6c09ac5d' => ['6073d0ab86b02' => 2],
        '605ae6c105eb8' => ['6073d356870a0' => 1],
        '605ae6c1651e9' => ['6073dcd9aea23' => 18, '6073de2a9a71e' => 46],
        '605ae6c321a5e' => ['60758bb51c7a7' => 1],
    ];
    const INITIAL_TWEET_COUNT = [
        '605ae6b104744' => [
            '60744fc8820e9' => 2
        ],
        '605ae6b162b5c' => [
            '60742d3a40420' => 4
        ],
        '605ae6b1f17dc' => [
            '6075958a28be2' => 9
        ],
        '605ae6b26e08b' => [
            '60743a29a5a3c' => 191
        ],
        '605ae6b2e5adf' => [
            '6074490cacaa6' => 2
        ],
        '605ae6b491646' => [
            '6074471c49af3' => 2
        ],
        '605ae6b503385' => [
            '607448791209d' => 3
        ],
        '605ae6b5ecf3c' => [
            '60744db6bc0f7' => 1
        ],
        '605ae6b70add0' => [
            '60743de8dfb08' => 2
        ],
        '605ae6b768e6a' => [
            '6074553a46bd9' => 2
        ],
        '605ae6b83162b' => [
            '607457f5caa44' => 1
        ],
        '605ae6bbafd5a' => [
            '607467355b73d' => 1
        ],
        '605ae6bdd53ee' => [
            '6074713ecf42b' => 1
        ],
        '605ae6be773a3' => [
            '6074733b6a26a' => 2
        ],
        '605ae6bff3449' => [
            '6073ce934fec1' => 6
        ],
        '605ae6c09ac5d' => [
            '6073d0ab86b02' => 1
        ],
    ];
	const ARTIST_SIZES = ['ITSI7299','ITSI7300','ITSI7301','ITSI7302','ITSI7303'];
	const BATTLE_ITEM_QUERY = 'SELECT item.id FROM item
        JOIN `user` ON item.`user` = `user`.id
        WHERE item.state = 1 AND `user`.user_type > 0 AND item.buy_state = 1
        AND item.regist_unix > 0 AND item_type = "IT367" AND item_type_sub = "ITSU5432"';


    const LIST_NOBORI_ITEM_TYPE = array(
        'IT303', 'IT304', 'IT305', 'IT306', 'IT307', 'IT308',
        'IT309', 'IT310', 'IT311', 'IT312', 'IT313', 'IT314',
        'IT315', 'IT316', 'IT317', 'IT318', 'IT319', 'IT320',
        'IT321', 'IT322', 'IT323', 'IT324', 'IT325', 'IT326',
        'IT327', 'IT328', 'IT329', 'IT330', 'IT331', 'IT442',
        'IT443', 'IT444', 'IT445', 'IT446', 'IT447', 'IT448',
        'IT449', 'IT450', 'IT451', 'IT452', 'IT453', 'IT454',
        'IT455', 'IT456', 'IT457', 'IT458', 'IT459', 'IT460',
        'IT461', 'IT462');

function seoTag($type)
	{
		global $sql;
		global $cc;

		if($seo = Globals::value("SEO_TAG"))
		{
			return $seo[$type];
		}

		//初期値の設定
//		$seo["title"] = "オリジナルTシャツ制作のUp-T ひんやり夏マスク/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
//		$seo["h1"] = "自分だけのオリジナルTシャツやiphoneケースなどのアイテムを作る・売る・買う";
		$seo["keywords"] = "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント";
//		$seo["description"] = "【利用者数日本一！】クラスTなどオリジナルTシャツや雑貨を1つから大量ロットまで作成できるUp-T(アップティー)。オリジナルデザインをWebやアプリで簡単に作成でき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
//		$seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)のひんやり夏マスク。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";

		//分岐
		$tmp = explode("/", $_SERVER["SCRIPT_NAME"]);
		$tmp = explode(".", $tmp[count($tmp) - 1]);
		$script = $tmp[0];

		switch($script)
		{
			case 'index':
                $seo["title"] = "オリジナルトートバッグ、エコバッグがデザインツールで1枚から作れる | グッズコンシェルオンデマンド";
                $seo["h1"] = "1枚からでもオリジナルTシャツが作れます";
                $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
                $seo["description"] = "デザインツールをつかってPC、スマホから画像をアップロードして1枚からオリジナルのトートバッグ、エコバッグが作れます！Tシャツや、タンブラー、水筒、マグカップも作れます！";
                break;
			case 'info':
				switch(Globals::get("type"))
				{
					case 'item':

						if($i_rec = $sql->selectRecord("item", Globals::get("id")))
						{
							$seo["title"] = $i_rec["name"]."│オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
							$seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                            $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
						}
						break 2;
					case 'user':

						if($i_rec = $sql->selectRecord("user", Globals::get("id")))
						{
							$seo["title"] = $i_rec["name"]."の通販TOP/Webやアプリで格安作成Up-T【最短即日】";
							$seo["h1"] = $i_rec["name"]."の商品一覧";
							$seo["description"] = sprintf('【利用者数日本一！】%sがオリジナルTシャツをはじめとするオリジナルグッズをUp-T(アップティー)で作成した商品一覧ページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業70年で品質も安心。',$i_rec["name"]);
							$seo["keywords"] = "";
							$seo["keywords"] .= $i_rec["name"].",マーケット,Tシャツ,販売,パーカー,スマホケース,up-t";
						}
						break 2;
					case 'pay':
						if(Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "ご注文内容│オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
							$seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                            $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
						}

						break 2;
                    case 'master_item_web_categories':

                        if($i_rec = $sql->keySelectRecord("master_item_web_categories",'name', Globals::get("name")))
                        {
                            if ($i_rec['name'] == 'オリジナルTシャツ') {
                                $seo["title"] = "オリジナルTシャツを1枚から格安で高品質プリント、無料デザイン作成【最短即日】";
                            } else {
                                $seo["title"] = $i_rec["name"] . "を1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                            }

                            $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで".$i_rec["name"]."にオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        }

                        break 2;
                    case 'master_item_web_sub_categories':

                        if($i_rec = $sql->keySelectRecord("master_item_web_sub_categories", 'name',Globals::get("name")))
                        {
                            $seo["title"] = "オリジナル".$i_rec["name"]."を1枚から格安で高品質プリント、無料デザイン作成【最短即日】";
                            $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで".$i_rec["name"]."にオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        }

                        break 2;
                    case 'master_item_type':

                        if($result = $sql->selectRecord("master_item_type", Globals::get("id"))) {
                            $seo["title"] = sprintf('%sのオリジナルプリント詳細 | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコンシェルオンデマンド', trim($result["name"]));
                            $seo["description"] = sprintf('%sについての詳細情報。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！', trim($result["name"]));
                        }

                        break 2;
					case 'post_new':

						if($i_rec = $sql->selectRecord("post_new", Globals::get("id")))
						{
							$seo["title"] = $i_rec["title"]." | オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
							$seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
						}
						break 2;
                    case 'post_new_category':
                        $seo["title"] = "おすすめ情報 | オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
                        $seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                        $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
                        break 2;
				}
				break;
			case 'search':
				switch(Globals::get("type"))
				{
                    case 'card_information':
                        $seo["title"] = "クレジットカード情報 | オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
                        $seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                        $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
                        break 2;
					case 'item':
						if(Globals::get("design")=="my" && Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "マイデザイン | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコン シェルオンデマンド";
							$seo["h1"] = "MYデザイン";
							$seo["description"] = "作成したデザインです。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！";
							$seo["keywords"] = "";
							$seo["keywords"] .= "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント";
						}else
						if(Globals::get("design")=="owner" && Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "二次利用作品一覧|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "二次利用作品一覧";
							$seo["description"] = "二次利用作品一覧|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["keywords"] = "";
							$seo["keywords"] .= "二次利用作品一覧,Tシャツ,販売,パーカー,スマホケース,up-t";
						}else
						if(!Globals::get("design") && Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "アイテム検索丨オリジナルTシャツ・オリジナルスマホケース販売のUp-T（アップティー）";
							$seo["h1"] = "アイテム検索";
							$seo["description"] = "アイテム検索のページです|オリジナルTシャツ・オリジナルスマホケースの販売ならUp-T（アップティー）オリジナルグッズを簡単に制作して無料で販売が出来ます【販売無料】【送料無料】【販売マーケットで簡単販売】";
							$seo["keywords"] = "";
							$seo["keywords"] .= "アイテム検索,Tシャツ,販売,パーカー,スマホケース,up-t";
						}else{
                            $keyWord = Globals::get('keyword');
							$seo["title"] = "条件検索結果ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
							$seo["h1"] = "条件検索結果ページ";
							$seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を1つから大量ロットまで作成できるUp-T(アップティー)の条件検索結果ページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業70年で品質も安心。";
							$seo["keywords"] = "";
							$seo["keywords"] .= "アイテム検索,Tシャツ,販売,パーカー,スマホケース,up-t";
                            if(!empty($keyWord)){
                                $seo["title"] = sprintf('%sに関するサイト内検索結果ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】',$keyWord);
                                $seo['h1'] = sprintf('%sに関するサイト内検索結果ページ',$keyWord);
                                $seo["description"] = sprintf('【利用者数日本一！】オリジナルTシャツや雑貨を1つから大量ロットまで作成できるUp-T(アップティー)の%sに関するサイト内検索結果ページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業70年で品質も安心。',$keyWord);
                            }
						}
						break 2;
					case 'pay':
						if(Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "注文履歴 | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド";
							$seo["h1"] = "注文履歴";
							$seo["description"] = "ご注文頂いた履歴情報です。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！";
							$seo["keywords"] = "";
							$seo["keywords"] .= "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント";
						}

						break 2;
					case 'user_fee':
						if(Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "報酬支払明細|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "報酬支払明細";
							$seo["description"] = "報酬支払明細|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["keywords"] = "";
							$seo["keywords"] .= "報酬支払明細,Tシャツ,販売,パーカー,スマホケース,up-t";
						}

						break 2;
					case 'user_fee':
						if(Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "報酬支払明細|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "報酬支払明細";
							$seo["description"] = "報酬支払明細|無料で作って、売って、買える！オリジナルTシャツUp-T";

							$seo["keywords"] = "";

							$seo["keywords"] .= "報酬支払明細,Tシャツ,販売,パーカー,スマホケース,up-t";
						}

						break 2;
					case 'item':

						break 2;
					case 'item_favorite':
						if( Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "お気に入りデザイン|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "お気に入りデザイン";
							$seo["description"] = "お気に入りデザイン|無料で作って、売って、買える！オリジナルTシャツUp-T";

							$seo["keywords"] = "";

							$seo["keywords"] .= "お気に入りデザイン,Tシャツ,販売,パーカー,スマホケース,up-t";
						}

						break 2;
                    case 'master_item_web_categories':
                            $seo["title"] = "アイテム一覧/オリジナルTシャツを簡単プリント制作・格安作成Up-T【格安】";
                            $seo["description"] = "【利用者数日本一！】60種類のアイテムと50種類のカラーのオリジナルTシャツにプリントが出来ます。ドライTシャツ、綿Tシャツ、i-phoneケース、スマホケース、スウェット等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";

                        break 2;
					case 'post_new':
						$seo["title"] = "コラム記事一覧/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
						$seo["description"] = $seo["meta_descripton"];

						break 2;
                    case 'voices':
                        $seo["title"] = "オリジナルＴシャツやオリジナルスマホケースのレビューページ【Up-T】アップティー";
                        if(Globals::get("page")== 1){
                            $seo["description"] = "オリジナルＴシャツレビュー│【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)の記事コラム。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        }elseif (Globals::get("page")== 2){
                            $seo["description"] = "オリジナルＴシャツレビュー2ページ│【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)の記事コラム。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        }else{
                            $seo["description"] = sprintf("オリジナルＴシャツレビュー%sページ│【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)の記事コラム。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。", Globals::get("page"));
                        }
                        break 2;
				}
				break;
			case 'edit':

				switch(Globals::get("type"))
				{
					case 'user':
						if( Globals::session("LOGIN_TYPE")=="user" && !Globals::get("design")){
							$seo["title"] = "アカウント設定 | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド";
							$seo["h1"] = "アカウント設定";
							$seo["description"] = "ご登録情報の編集・確認ページです。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1 枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！";

							$seo["keywords"] = "";

							$seo["keywords"] .= "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント ";
						}
						if( Globals::session("LOGIN_TYPE")=="user" && Globals::get("design")=="pass"){
							$seo["title"] = "パスワード変更│オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
							$seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                            $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
						}
                        if (Globals::session("LOGIN_TYPE") == "user" && Globals::get("design") == "delete") {
                            $seo["title"]       = "退会依頼フォーム | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド ";
                            $seo["h1"]          = "退会手続き";
                            $seo["description"] = "退会依頼 | グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！";

                            $seo["keywords"] = "";

                            $seo["keywords"] .= "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント ";
                        }



						break 2;
					case 'item':
						if( Globals::session("LOGIN_TYPE")=="user" ){
							$seo["title"] = "デザイン設定|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "デザイン設定";
							$seo["description"] = "デザイン設定|無料で作って、売って、買える！オリジナルTシャツUp-T";

							$seo["keywords"] = "";

							$seo["keywords"] .= "デザイン設定,Tシャツ,販売,パーカー,スマホケース";
						}

						break 2;
                    case 'store_info':
                        $seo["title"] = "配送元登録 │オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！";
                        $seo["description"] = "オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！";
                        $seo["keywords"] = "エコバッグ,トートバッグ,1枚,Tシャツ、タンブラー,オリジナルプリント";
                        break 2;

				}
				break;
			case 'view':
				switch(Globals::get("type"))
				{
					case 'report':
						if(Globals::session("LOGIN_TYPE")=="user"){
							$seo["title"] = "売上レポート|無料で作って、売って、買える！オリジナルTシャツUp-T";
							$seo["h1"] = "売上レポート";
							$seo["description"] = "売上レポート|無料で作って、売って、買える！オリジナルTシャツUp-T";

							$seo["keywords"] = "";

							$seo["keywords"] .= "売上レポート,Tシャツ,販売,パーカー,スマホケース";
						}

						break 2;
				}


				break;
			case 'login':

				$seo["title"] = "ログイン | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド";
				$seo["h1"] = "ログイン";
				$seo["description"] = "ログイン | オリジナルトートバッグやオリジナルエコバッグが1枚からプリントして作れます！タンブラーやボトルなども1個から！すべてフルカラー印刷できるオンデマンドサービス";

                $seo["keywords"] = "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント";


				break;

			case 'regist':

				switch(Globals::get("type"))
				{
					case 'user':

							$seo["title"] = "新規会員登録 | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド";
							$seo["h1"] = "新規登録";
							$seo["description"] = "新規会員登録 | オリジナルトートバッグやオリジナルエコバッグが1枚からプリントして作れます！タンブラーやボトルなども1個から！すべてフルカラー印刷できるオンデマンドサービス";


							$seo["keywords"] = "トートバッグ,エコバッグ,タンブラー,マグカップ,プリント";

						break 2;
					case 'pay':

							$seo["title"] = "カートステップ２| オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド";
							$seo["h1"] = "カートステップ２";
							$seo["description"] = "カートステップ２|トートバッグやエコバッグが1枚からオリジナルプリントして作れます！タンブラーやボトルなども1個から！すべてフルカラー印刷できるオンデマンドサービス";
							$seo["keywords"] = "カートステップ２,トートバッグ,エコバッグ,プリント,タンブラー,マグカップ";

						break 2;
                    case 'store_info':

                        $seo["title"] = "発送元管理 | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコン シェルオンデマンド ";
                        $seo["description"] = "発送元の管理ができます。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます。";

                        break 2;

                    //sendmail
                    case 'sendmail':
                        $seo["title"] = "お問い合わせページ（Ｑ＆Ａ）│ Cbox | Ondemand";
                        $seo["description"] = "Up-T（アップティー）のお問い合わせページとなります。Ｔシャツからスマホまで1000種類以上のアイテムとカラー、サイズが販売されています。オリジナルTシャツをWebやアプリで格安作成Up-T【最短即日】";
                        break 2;
				}


				break;
			case 'page':
				$page_arr=array();
				$file = new SplFileObject("page.csv");
				$file->setFlags(SplFileObject::READ_CSV);
				$is_first=true;
				foreach ($file as $line) {
				 	//終端の空行を除く処理　空行の場合に取れる値は後述
					if(!is_null($line[0]) && !$is_first){
						$page_arr[$line[0]]["title"]=mb_convert_encoding($line[1],'UTF-8','Shift-JIS');
						$page_arr[$line[0]]["h1"]=mb_convert_encoding($line[2],'UTF-8','Shift-JIS');
						$page_arr[$line[0]]["keywords"]=mb_convert_encoding($line[3],'UTF-8','Shift-JIS');
						$page_arr[$line[0]]["description"]=mb_convert_encoding($line[4],'UTF-8','Shift-JIS');
				 	}
				 	$is_first=false;
				}
				if(isset($page_arr[Globals::get("p")])){
					$seo=$page_arr[Globals::get("p")];
//					break;
				}


				switch(Globals::get("p"))
				{
                    case 'news':
                        $seo["title"] = 'おすすめ情報 | オリジナルグッズコンシェルなら1枚からオリジナルエコバッグが作れる！';
                        $seo["description"] = 'オリジナルエコバッグやトートバッグ、Tシャツ、タンブラーなどが1個からフルカラープリントで作れる！ご注文から4営業日で発送！デザインツールでかんたん作成！';
                        $seo["keywords"] = 'トートバッグ,エコバッグ,プリント,タンブラー,マグカップ';
                        break;
                    case 'cart':
                        $seo["title"] = 'カートステップ１| オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオンデマンド';
                        $seo["description"] = 'トートバッグやエコバッグが1枚からオリジナルプリントして作れます！タンブラーやボトルなども1個から！す べてフルカラー印刷できるオンデマンドサービス';
                        $seo["keywords"] = 'トートバッグ,エコバッグ,プリント,タンブラー,マグカップ';
                        break;
                    case 'error':
                        $seo["title"] = 'エラー | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコンシェルオンデマンド';
                        $seo["keywords"] = 'トートバッグ,エコバッグ,タンブラー,マグカップ,プリント';
                        $seo["description"] = 'エラーです。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！';
                    	break;
                    case 'faq':
                        $seo["title"] = '良くある質問 | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコン シェルオンデマンド';
                        $seo["keywords"] = 'トートバッグ,エコバッグ,タンブラー,マグカップ,プリント ';
                        $seo["description"] = 'グッズコンシェルオンデマンドサービスをご利用にあたっての良くある質問をまとめました。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！';
                        break;
					case 'market':
					case 'market_collaboration':
					case 'market_rankings':
					case 'market_whats_new':
						$seo["title"] = "Cbox | Ondemand | Market";
						$seo["h1"] = "オリジナルTシャツ・パーカーを買える面白マーケット【Up－T】【最安値】";
						$seo["keywords"] = "購入, 買う,販売,Tシャツ,パーカー,スマホケース,iphoneケース,Up-T,アップティー";
						$seo["description"] = "オリジナルTシャツやパーカーを販売している日本最大級のTシャツマーケット【Up－T】アップティー。あなたも無料でTシャツ販売が出来ます【最安値】";
						break;
					case 'business_customer':
						$seo["title"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "イベントのプロモーション、スタッフ制服、アパレル販売用などのオリジナルアイテムプリントならup-tにお任せください！ 「納期」「予算」「品質」に合わせて最適なプランをご提案いたします";
                    case 'item_detail':
                        $seo["title"] = '商品ラインナップ | オリジナルトート、エコバッグ、Tシャツ、タンブラーなど1個から作成！グッズコンシェルオンデマンド';
                        $seo["description"] = 'グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャ ツやタンブラー、水筒なども1個から製作できます！';
                        $seo["keywords"] = 'トートバッグ,エコバッグ,タンブラー,マグカップ,プリント ';
                        break;
					case 'designfree':
						$seo["title"] = "絵がかけない方、イラストが作れない方デザイン無料でオリジナルTシャツを制作致します。";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "絵がかけない方、イラストが作れない方デザイン無料でオリジナルTシャツを制作致します。オリジナルTシャツからオリジナルパーカー、オリジナルタオルやiphoneケースまであらゆるデザインをUp-Tのプロのイラストレーターが行います";
						break;

					case 'tshirt_00085_cvt':
						$seo["title"] = "キッズTシャツ・00085-CVT全５０色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "定番Ｔシャツのキッズサイズです。カラーバリエーションは全50色。お好きなデザインで制作できます。";
						break;
					case 'tshirt_slim_030':
						$seo["title"] = "スリムTシャツ・DM030全４０色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "人気の高いスタンダードタイプのスリムTシャツ。カラーバリエーションは全40色。お好きなデザインで制作できます。";
						break;
					case 'poloshirt_polopock_100':
						$seo["title"] = "定番ポロシャツ（ポケット付き）・00100-VP全２４色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "ポロシャツの王道定番ポロシャツのポケット付き。カラーバリエーションは全24色。お好きなデザインで制作できます。";
						break;
					case 'longtshirt_00169':
						$seo["title"] = "長袖ポロシャツ（ポケット付き）・00169-VLP全１６色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "ポロシャツシリーズの定番を長袖で。カラーバリエーションは全１６色。お好きなデザインで制作できます。";
						break;
					case 'tshirt_kids_085':
						$seo["title"] = "キッズTシャツ・00085-CVT全５０色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "定番Ｔシャツのキッズサイズです。カラーバリエーションは全50色。お好きなデザインで制作できます。";
						break;
					case 'tshirt_vneck_502':
						$seo["title"] = "VネックTシャツ・DM502全６色｜オリジナルＴシャツを簡単プリント制作・格安作成Up-t【格安】";
						$seo["h1"] = "法人様・大口注文・業者様窓口│オリジナルTシャツを簡単自作・無料販売Up－T【最安値】";
						$seo["keywords"] = "オリジナルTシャツ,オリジナル,Tシャツ,ユニフォーム,イベント";
						$seo["description"] = "スリムなシルエットでファッショナブルなVネックTシャツ。カラーバリエーションは全６色。お好きなデザインで制作できます。";
						break;

                    //tv_collabo
                    case 'tv_collabo':
                        $seo["title"] = "テレビコラボ-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のテレビコラボについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //tv_collabo_present
                    case 'tv_collabo_present':
                        $seo["title"] = "テレビコラボプレゼント-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のテレビコラボプレゼントについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //price_report
                    case 'price_report':
                        $seo["title"] = "お見積りシミュレーション-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のお見積りシミュレーションについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //select-cart-specification
                    case 'select-cart-specification':
                        $seo["title"] = "のぼり仕様オプション-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)ののぼり仕様オプションについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //smartphonecase_business_customer
                    case 'smartphonecase_business_customer':
                        $seo["title"] = "大口・法人窓口-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の大口・法人窓口についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //phone
                    case 'phone':
                        $seo["title"] = "オリジナルスマホケース-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のオリジナルスマホケースについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //halloween
                    case 'halloween':
                        $seo["title"] = "ハロウィン特集-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のハロウィン特集についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //feature_list
                    case 'feature_list':
                        $seo["title"] = "特集一覧-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の特集一覧についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //results
                    case 'results':
                        $seo["title"] = "検索結果-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の検索結果についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //reminder
                    case 'reminder':
                        $seo["title"] = "パスワード再発行 | オリジナルトート、エコバッグ、グッズを1個から作成！グッズコンシェルオン デマンド ";
                        $seo["description"] = "パスワードの再発行フォームです。グッズコンシェルオンデマンドサービスは、エコバッグ、トートバッグが1枚から製作できるサービスです。Tシャツやタンブラー、水筒なども1個から製作できます！";
                        break;
                    //policy
                    case 'policy':
                        $seo["title"] = "利用規約-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の利用規約についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //tool_howto
                    case 'tool_howto':
                        $seo["title"] = "Tシャツデザインツールの使い方-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のTシャツデザインツールの使い方についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //jquality
                    case 'jquality':
                        $seo["title"] = "オリジナルTシャツ制作のUp-T 丸井織物は日本の国家Project J-QUALITY加入企業です /オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)の記事コラム。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    //base
                    case 'base':
                        $seo["title"] = "BASE連携-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のBASE連携についてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //design_store
                    case 'design_store':
                        $seo["title"] = "オリジナルショップ機能-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のオリジナルショップについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //original_daily
                    case 'original_daily':
                        $seo["title"] = "即日発送アイテム-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のオリジナル即日発送アイテムについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //face_mask
                    case 'face_mask':
                        $seo["title"] = "オリジナルプリントマスク-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のオリジナルプリントマスクについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //purchase-plain
                    case 'purchase-plain':
                        $seo["title"] = "無地Tシャツ-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の無地Tシャツについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //search-stock
                    case 'search-stock':
                        $seo["title"] = "無地Tシャツの在庫一覧-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)の無地Tシャツの在庫一覧ページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //market_pickup
                    case 'market_pickup':
                        $seo["title"] = "マーケットピックアップ-オリジナルTシャツ制作のUp-T";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツや雑貨を作成できるUp-T(アップティー)のマーケットピックアップについてのページ。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。創業80年で品質も安心。";
                        break;
                    //cool-summer-mask
                    case 'cool-summer-mask':
                        $seo["title"] = "オリジナルTシャツ制作のUp-T ひんやり夏マスク/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)のひんやり夏マスク。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    //porular-tshirt
                    case 'popular-tshirt':
                        $seo["title"] = "オリジナルTシャツ制作のUp-T 全面プリントアイテム/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)の全面プリントアイテム。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    //team
                    case 'team':
                        $seo["title"] = "オリジナルTシャツ制作のUp-T オリジナルTシャツのチーム・部活Tシャツ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)のチーム・部活Tシャツ。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    //volleyball
                    case 'volleyball':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーでバレーボール部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //shogi
                    case 'shogi':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで将棋部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //baseball
                    case 'baseball':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで野球・ソフトボール部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //soccer
                    case 'soccer':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーでサッカー部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //basketball
                    case 'basketball':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーでバスケ部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //dance
                    case 'dance':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで陸上・ランニング部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //running
                    case 'running':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで陸上・ランニング部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //brassband
                    case 'brassband':
                        $seo["title"] = "Cbox | Ondemand";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで吹奏楽部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //lightmusic
                    case 'lightmusic':
                        $seo["title"] = "軽音学部オリジナルTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで軽音楽部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //broadcast
                    case 'broadcast':
                        $seo["title"] = "放送部オリジナルTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで放送部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //calligraphy
                    case 'calligraphy':
                        $seo["title"] = "書道部オリジナルTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで書道部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //teamart
                    case 'teamart':
                        $seo["title"] = "美術部オリジナルTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで美術部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //drama
                    case 'drama':
                        $seo["title"] = "演劇部オリジナルTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーで演劇部オリジナルTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                    //classtshirts
                    case 'classtshirts':
                        $seo["title"] = "クラスTシャツを1枚からWebやアプリで格安プリント作成 | Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】60種類のおすすめアイテムと50種類のカラーでクラスTシャツにオシャレで高品質なプリントが1枚から出来ます。その他ドライTシャツ等があります。Up-TはWebやアプリで簡単にデザインでき、どこよりも格安で即日発送、送料無料。創業80年で品質も安心。";
                        break;
                        case 'compareItem':
                        $seo["title"] = "アイテムを比較する│オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】オリジナルTシャツ・クラスTを1つから大量ロットまで作成できるUp-T(アップティー)のアイテム比較ページ。Webやアプリで簡単にデザインでき、どこよりも格安で送料完全無料。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'battle':
                        $seo["title"] = "【T-1グランプリ開催！！】よしもと芸人80名がオリジナルTシャツを作成して販売バトル！！優勝を狙え！！一般ユーザも参加可能";
                        $seo["thumbnail"] = "https://up-t.jp/common/img/common/thumbnail.jpg";
                        $seo["description"] = "よしもと興行の芸人80名がオリジナルTシャツを作って販売バトル！！ファンのTシャツを購入して1位を目指せ！！購入やツイートして応援するT1グランプリ開催！4月15日よりスタート";
                        $seo["keywords"] = "T1グランプリ,よしもと興行,かまいたち,オリジナルTshatu";
                        break;
                    case 'kamaitachi':
                        $seo["title"] = "かまいたちのUp-T（アップティー）CMシアター|オリジナルTシャツのUp-T（アップティ）";
                        $seo["description"] = "Up-Tイメージキャラクターのかまいたちの山内健司さん、濱家隆一さんの特集ページです。Up-Tの自分で簡単に作れるオリジナルTシャツシステムと無料で販売できる販売システムについて面白く説明してもらってます。";
                        break;
                    case 'hkrk_idle':
                        $seo["title"] = "ほくりくアイドル部マンスリーTシャツチャレンジ|オリジナルTシャツのアップティー";
                        $seo["description"] = "ほくりくアイドル部が本気でデザイン！Tシャツ販売キャンペーン。ほくりくアイドル部メンバーの手書きデザインTシャツが毎月登場!!!オリジナルTシャツのUp-Tとのコラボ企画！";
                        break;
                    case 'ranking2':
                        $seo["title"] = "オリジナルTシャツ販売バトルT1グランプリ第2回中間発表";
                        $seo["description"] = "T1グランプリ第二回中間発表のページです。今回のトップはすゑひろがりず様です。";
                        break;
                    case 'howtoprint':
                        $seo["title"] = "オリジナルTシャツUp-Tのプリント方法一覧/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)のインクジェット印刷の解説ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'inkjet':
                        $seo["title"] = "オリジナルTシャツUp-Tのインクジェットプリント解説ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)のシルクスクリーン印刷の解説ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'siilscreeen':
                        $seo["title"] = "オリジナルTシャツUp-Tのシルクスクリーンプリント解説ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)の昇華転写の解説ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'sublimationtransfer':
                        $seo["title"] = "オリジナルTシャツUp-Tの昇華転写プリント解説ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)のUVインクジェット印刷の解説ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'uvinkjet':
                        $seo["title"] = "オリジナルTシャツUp-TのUVインクジェットプリント解説ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)のフルカラー印刷の解説ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'fullcolor':
                        $seo["title"] = "オリジナルTシャツUp-Tのフルカラープリント解説ページ/オリジナルTシャツ、グッズを格安作成Up-T【最短即日】";
                        $seo["description"] = "【利用者数日本一！】1つから大量ロットまで作成できるUp-T(アップティー)のプリント方法一覧ページ。業者、学校、グループ活動で必要なオリジナルグッズを格安作成。Tシャツやスマホケース等、2000種類から選択可能。創業80年で品質も安心。";
                        break;
                    case 'uuum':
                        $seo["title"] = 'UUUM所属スポーツ系クリエーター大集合！ユニフォームデザイングランプリ│オリジナルTシャツのUp-T・Wundouプレセンツ';
                        $seo["description"] = '頑張れニッポン！！スポーツの祭典,UUUM所属スポーツ系クリエーター大集合！ユニフォームデザイングランプリ！！ユーザーもユニフォームデザインで参加可能。豪華賞品もプレゼント';
                        break;
                    case 'atjam':
                        $seo["title"] = '【THE FASHIONIST】Up-T×＠JAM EXPO2020-2021 ar雑誌出演争奪バトル！！！';
                        $seo["description"] = 'みんなで推しを応援しよう！！総勢30名以上のアイドルが、Up-TでTシャツ販売数などで、ar雑誌出演をかけて競います！【THE FASHIONIST】Up-T×＠JAM EXPO2020-2021 ar雑誌出演争奪バトル！！！';
                        break;
                    case 'kurokora':
                        $seo["title"] = 'クロちゃん×up-tコラボ！クロコラTシャツグランプリ！';
                        $seo["description"] = 'up-tコラボ、クロコラTシャツグランプリは、松竹芸能所属のクロちゃんの様々な写真をコラージュしてTシャツにして販売するグランプリです！豪華賞品プレゼントあり！ぜひup-tにご参加ください！';
                        break;
                        case 'heytaxi':
                            $seo["title"] = 'かまいたちのヘイ！タクシー！×オリジナルTシャツアップティー公式グッズサイト';
                            $seo["description"] = 'オリジナルTシャツのup-t（アップティー）とかまいたちのヘイ！タクシー！が組んで公式グッズを販売。かまいたちがup-tの工場まで訪問して厳選したグッズをデザイン！販売します。';
                            break;

					case 'lespros':
						$seo["title"] = 'レプロモデルが制作したオリジナルグッズ│オリジナルTシャツのUp-T（アップティー）';
						$seo["description"] = 'レプロエンターテイメントのモデル青島妃菜、麻生果恩、ケリーアン、一ノ瀬陽鞠がつくったオリジナルグッズデザインです。Tシャツやパーカー、スマホケースやスマホリングなど様々なおしゃれでかわいいグッズが満載。';
						break;
					case 'item_lespros':
						$seo["title"] = 'レプロモデルが制作したオリジナルグッズ│オリジナルTシャツのUp-T（アップティー）';
						$seo["description"] = 'レプロエンターテイメントのモデル青島妃菜、麻生果恩、ケリーアン、一ノ瀬陽鞠がつくったオリジナルグッズデザインです。Tシャツやパーカー、スマホケースやスマホリングなど様々なおしゃれでかわいいグッズが満載。';
						break;
					case 'bunbunbowl':
						$seo["title"] = 'オリジナルTシャツのアップティーによるぶんぶんボウルの休み時間コラボグッズを販売！！';
						$seo["description"] = 'オリジナルTシャツのアップティー×ぶんぶんボウルの休み時間がコラボ！';
						break;
					case 'uuumfes':
						$seo["title"] = 'オリジナルTシャツのUp-T×クリエーターコラボグッズフェス！頂点を決めろ!!!THE GOODS GRANPRIX';
						$seo["description"] = 'オリジナルTシャツのUp-T（アップティー）が、タケヤキ翔、SKJ Villege、ぁぃぁぃとコラボ！Tシャツやスマホケース、パーカーなど様々なアイテムを販売！！グランプリで１位を目指せ！！';
						break;
                    default:
						break 2;
						
				}
		}

		Globals::setValue("SEO_TAG", $seo);

		return $seo[$type];
	}

	function is_ssl()
	{
		if ( isset($_SERVER['HTTPS']) === true ) // Apache
		{
			return ( $_SERVER['HTTPS'] === 'on' or $_SERVER['HTTPS'] === '1' );
		}
		elseif ( isset($_SERVER['SSL']) === true ) // IIS
		{
			return ( $_SERVER['SSL'] === 'on' );
		}
		elseif ( isset($_SERVER['HTTP_X_FORWARDED_PROTO']) === true ) // Reverse proxy
		{
			return ( strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https' );
		}
		elseif ( isset($_SERVER['HTTP_X_FORWARDED_PORT']) === true ) // Reverse proxy
		{
			return ( $_SERVER['HTTP_X_FORWARDED_PORT'] === '443' );
		}
		elseif ( isset($_SERVER['SERVER_PORT']) === true )
		{
			return ( $_SERVER['SERVER_PORT'] === '443' );
		}

		return false;
	}

	function getSelfFlag($item)
	{

		if($self = Globals::session("SELF_ITEM"))
		{
            if(isset($self[$item])){
                return true;
			}
		}
		return false;
	}

	function setSelfFlag($item)
	{
		$self = Globals::session("SELF_ITEM");
		$self[$item] = time();
		Globals::setSession("SELF_ITEM", $self);
	}

	function selfKey($item_rec)
	{
		return sha1($item_rec["id"].$item_rec["user"]."r57i335b");
	}

	function checkItemFavorite($user, $item)
	{
		global $sql;

		$table = "item_favorite";
		$where = $sql->setWhere($table, null, "user", "=", $user);
		$where = $sql->setWhere($table, $where, "item", "=", $item);
		if($sql->getRow($table, $where)) return true;
		return false;
	}

	function getAgencyUserList($agency, $data_type)
	{
		global $sql;

		$table = "user";
		$where = $sql->setWhere($table, null, "agency", "=", $agency);
		$order = $sql->setOrder($table, null, "id", "ASC");
		$result = $sql->getSelectResult($table, $where, $order);

		$list = array();
		while($rec = $sql->sql_fetch_assoc($result))
		{
			switch($data_type)
			{
				case 'id':
					$list[] = $rec["id"];
					break;
				case 'all':
					$list[] = $rec;
					break;
			}
		}
		return $list;
	}

	function changeItemState($id, $state)
	{
		global $sql;

		$table = "item";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["state"] != $state)
		{
			$update = $sql->setData($table, null, "state", $state);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	function changeItemCartState($id, $state)
	{
		global $sql;

		$table = "item";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["cart_state"] != $state)
		{
			$update = $sql->setData($table, null, "cart_state", $state);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	function changeUserFee($id, $state)
	{
		global $sql;

		$table = "user_fee";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["state"] != $state)
		{
			$update = $sql->setData($table, null, "state", $state);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	function changePayPay($id, $state, $notification = false, $status_notification = null)
	{
		global $sql;

		$table = "pay";
		if(!$rec = $sql->selectRecord($table, $id)) return false;
        if(!empty($rec['parent_pay_id'])) return false;
        $accepted_pay_types = ['rakuten', 'sb', 'docomo', 'au', 'linepay', 'apple_pay'];
		if($rec["pay_state"] != $state || in_array($rec["pay_type"], $accepted_pay_types))
		{
			switch($rec["pay_type"])
			{
				case 'card':

					switch($state)
					{
						case 1:
							//実売上
							if(!$rec["charge_log"]) return false;
							if(!gmoFunc::auth2saleEntry($rec["charge_log"])) return false;

							break;

						case 2:

							//キャンセル
							if(!$rec["charge_log"]) return false;
							if(!gmoFunc::sale2cancelEntry($rec["charge_log"])) return false;
							break;

						default:
							return false;
					}
					break;

                case 'amazon_pay':

                    switch($state)
                    {
                        case 1:
                        	break;
                        case 2:
							$order = $sql->selectRecord('pay', $rec['id']);
							try {
								refundAmazon($order['pay_total'], $order['amazon_capture_id']);
							} catch (\Exception $e){
								$error = $e->getMessage() ."</li>\n";
								Globals::setPost("step", Globals::post("step") - 1);
							}
                            break;

                        default:
                            return false;
                    }
                    break;

				case 'conveni':

					switch($state)
					{
						case 0:
							break;
						case 1:
						case 2:
							break;

						default:
							return false;
					}
					break;

				case 'bank':

					switch($state)
					{
						case 0:
							break;
						case 1:
						case 2:
							break;

						default:
							return false;
					}
					break;

				case 'cod':

					switch($state)
					{
						case 0:
						case 1:
						case 2:
							break;

						default:
							return false;
					}
					break;

				case 'ponpare':

					switch($state)
					{
						case 0:
						case 1:
						case 2:
							break;

						default:
							return false;
					}
					break;

				case 'after':

					switch($state)
					{
                        case 1:
                            $al_table = "after_log";
                            if (!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"])) return false;
                            if ($al_rec["state"] != 12 && $al_rec["state"] != 2) return false;

                            $update = $sql->setData($al_table, null, "state", 11);
                            $sql->updateRecord($al_table, $update, $al_rec["id"]);

                            $update = $sql->setData($table, null, "pay_state", 1);
                            $update = $sql->setData($table, $update, "conf_date", date("Y-m-d H:i:s"));
                            $sql->updateRecord($table, $update, $rec["id"]);

                            break;
						case 2:
							//キャンセル
							$al_table = "after_log";
							if(!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"])) return false;
							if($al_rec["state"] != 11) return false;
							if($al_rec["gmo_transaction_id"] == "") return false;

							$buyer = array(
								"gmoTransactionId" => $al_rec["gmo_transaction_id"],
								"shopTransactionId" => "",
								"shopOrderDate" => "",
								"fullName" => "",
								"fullKanaName" => "",
								"zipCode" => "",
								"address" => "",
								"companyName" => "",
								"departmentName" => "",
								"tel1" => "",
								"tel2" => "",
								"email1" => "",
								"email2" => "",
								"billedAmount" => "",
								"gmoExtend1" => "",
								"paymentType" => 2
											);
							$deliverycustomer = array(
								"fullName" => "",
								"fullKanaName" => "",
								"zipCode" => "",
								"address" => "",
								"companyName" => "",
								"departmentName" => "",
								"tel" => ""
							);
							$detail = array();
							$detail[] = array(
								"detailName" => "",
								"detailPrice" => "",
								"detailQuantity" => "",
								"gmoExtend2" => "",
								"gmoExtend3" => "",
								"gmoExtend4" => ""
							);

							$data = gmoFunc::afterTransactionCancel($buyer, $deliverycustomer, $detail);

							if(!is_object($data)) return false;
							$data_array = obj2arr($data);

							$update = null;
							switch ($data_array["result"])
							{
								case 'OK':
									$update = $sql->setData($al_table, $update, "state", 5);//取引キャンセル
									$sql->updateRecord($al_table, $update, $al_rec["id"]);

									$update = $sql->setData($table, null, "delivery_state", 2);
									$sql->updateRecord($table, $update, $rec["id"]);
									break;
								case 'NG':
									$update = $sql->setData($al_table, $update, "state", 3);
									$error = $data_array["errors"]["error"]["errorMessage"];
									$update = $sql->setData($al_table, $update, "error_massage", $error);
									$sql->updateRecord($al_table, $update, $al_rec["id"]);
									break;
							}
							break;

						default:
							return false;
					}
					break;
				case 'after2':

					switch($state)
					{
						case 1:
							$al_table = "after_log2";
							if(!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"])) return false;
							if($al_rec["state"] != 12 && $al_rec["state"] != 2 ) return false;

							$update = $sql->setData($al_table, $update, "state", 11);
							$sql->updateRecord($al_table, $update, $al_rec["id"]);

							$update = $sql->setData($table, null, "pay_state", 1);
							$update = $sql->setData($table, null, "conf_date", date("Y-m-d H:i:s"));
							$sql->updateRecord($table, $update, $rec["id"]);

//オーナーにメール送信
						// $pay_item_table = "pay_item";
						// $where = $sql->setWhere($pay_item_table, null, "pay", "=", $rec["id"]);
						// $order = $sql->setOrder($pay_item_table, null, "regist_unix", "ASC");
						// $result_tmp = $sql->getSelectResult($pay_item_table, $where, $order);
						// while($tmp_rec = $sql->sql_fetch_assoc($result_tmp))
						// {
						// 	if($tmp_rec["item"])
						// 	{
						// 		if($tmp2_rec = $sql->selectRecord("item", $tmp_rec["item"]))
						// 		{
						// 			if($tmp2_rec["user"] && $tmp2_rec["user"] != $tmp_rec["buy_user"])
						// 			{
						// 				if($tmp3_rec = $sql->selectRecord("user", $tmp2_rec["user"]))
						// 				{
						// 					mail_templateFunc::sendMail("user", "item_pay", $tmp3_rec["mail"], $tmp_rec);
						// 				}
						// 			}
						// 		}
						// 	}
						// }



							break;

						case 2:
							//キャンセル
							$al_table = "after_log2";

                            if ($notification == 'true') {
                                if (!empty($status_notification) && $status_notification == 'NG') {
                                    $update = $sql->setData($table, null, "delivery_state", 2);
                                    $sql->updateRecord($table, $update, $rec["id"]);
                                }
                            }
							if(!$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"])) return false;
							if($al_rec["state"] != 11) return false;
							if($al_rec["systemOrderId"] == "") return false;

							$url = "https://www.atobarai.jp/api/cancel/rest";

							$postdata = array();
							$postdata["EnterpriseId"]=urlencode("29182");
							$postdata["ApiUserId"]=urlencode("591");
							$postdata["OrderId[]"]=urlencode($al_rec["systemOrderId"]);

							$ch = curl_init($url);
							curl_setopt($ch, CURLOPT_POST, true);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
							$result = curl_exec($ch);
							curl_close($ch);
							$result_xml=simplexml_load_string($result);
							/////////////////////////////
							if($result_xml->status=="error"){
								// $update = $sql->setData($al_table, $update, "state", 3);
								// $error ="";
								// $messages=$result_xml->messages->message;
								// foreach ($messages as $key => $value) {
								// 	$error .= "<li>".$value."</li>\n";
								// }
								// $update = $sql->setData($al_table, $update, "error_massage", $error);
								// $sql->updateRecord($al_table, $update, $al_rec["id"]);
								break;
							}else if($result_xml->status=="success"){
								$update = $sql->setData($al_table, $update, "state", 5);//取引キャンセル
								$sql->updateRecord($al_table, $update, $al_rec["id"]);

                                $update = $sql->setData($table, null, "delivery_state", 2);
                                $sql->updateRecord($table, $update, $rec["id"]);
                                break;
							}

							break;

						default:
							return false;
					}
					break;
				case 'rakuten':

					switch($state)
					{
						case 1:
							//実売上
							if ($notification == 'true') {
								$table_charge_log = "charge_log";
								if (!$rec["charge_log"]) return false;
								if (!$cl_rec_charge_log = $sql->selectRecord($table_charge_log, $rec["charge_log"])) return false;
								$update_charge_log = $sql->setData($table_charge_log, null, "state", 2);
								$update_charge_log = $sql->setData($table_charge_log, $update_charge_log, "regist_unix", time());
								$sql->updateRecord($table_charge_log, $update_charge_log, $cl_rec_charge_log["id"]);

								break;
							} else {
								if (!$rec["charge_log"]) return false;
								if (!gmoFunc::auth2saleEntryRakuten($rec["charge_log"])) {
									return false;
								}

								return true;
							}

						case 2:

							//キャンセル
							if ($notification == 'true') {
								if (!empty($status_notification) && $status_notification == 'PAYFAIL') {
									if (!$rec["charge_log"]) return false;
									if (!gmoFunc::sale2cancelEntryRakuten($rec["charge_log"])) {
										return false;
									}
								}
								$table_charge_log = "charge_log";
								if (!$rec["charge_log"]) return false;
								if (!$cl_rec_charge_log = $sql->selectRecord($table_charge_log, $rec["charge_log"])) return false;
								if ($cl_rec_charge_log['state'] != 3) {
									$update_charge_log = $sql->setData($table_charge_log, null, "state", 5);
									$update_charge_log = $sql->setData($table_charge_log, $update_charge_log, "regist_unix", time());
									$sql->updateRecord($table_charge_log, $update_charge_log, $cl_rec_charge_log["id"]);
								}

								break;
							} else {
								if (!$rec["charge_log"]) return false;
								if (!gmoFunc::sale2cancelEntryRakuten($rec["charge_log"])) {
									return false;
								}

								return true;
							}

						default:
							return false;
					}
					break;
                case 'sb':
                case 'docomo':
                case 'au':
                case 'linepay':
                    switch ($state) {
                        case 1:
                            if (!gmoFunc::updateCareerPayment($rec["charge_log"])) {
                            	return false;
                            }

                            break;
                        case 2:
                            if (!gmoFunc::updateCareerPayment($rec["charge_log"], 4)) {
                                return false;
                            }

                            break;
                        default:
                            return false;
                    }
                    break;
				case 'apple_pay':
                    if(!$rec["charge_log"]) return false;

                    switch($state)
                    {
                        case 1:
                            //実売上
                            if(!gmoFunc::updateApplePay($rec["charge_log"])) return false;

                            break;
                        case 2:
                            if(!gmoFunc::updateApplePay($rec["charge_log"], 4)) return false;
                            break;
                        default:
                            return false;
                    }
                    break;
				case 'pay':

					switch($state)
					{
						case 1:
							//実売上
							if(!$rec["charge_log"]) return false;
							if(!gmoFunc::auth2saleEntryPayPay($rec["charge_log"])) return false;

							break;

						case 2:

							//キャンセル
							if(!$rec["charge_log"]) return false;
							if(!gmoFunc::sale2cancelEntryPayPay($rec["charge_log"])) return false;
							break;

						default:
							return false;
					}
					break;
                default:
					return false;
			}
		}

		$update = $sql->setData($table, null, "pay_state", $state);
		$sql->updateRecord($table, $update, $rec["id"]);

		//関連データのステータスも同時変更
		switch($state)
		{
			case 0:
			case 2:

				$table = "pay_item";
				$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
				$update = $sql->setData($table, null, "state", 0);
				$sql->updateRecordWhere($table, $update, $where);

				$table = "pay_option";
				$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
				$update = $sql->setData($table, null, "state", 0);
				$sql->updateRecordWhere($table, $update, $where);

				//Return point to user that has using point
				if ($state == 2) {
                    if (empty($rec['design_store_id'])) {
                        changePointLog($rec["id"], $rec['user'], 0);
                    }
		        }
				//Return promotion code
				$promotion_code_table = "promotion_code";
				$promotion_code_where = $sql->setWhere($promotion_code_table, null, "pay", "=", $rec["id"]);
				$promotion_code_update = $sql->setData($promotion_code_table, null, "state", 0);
				$promotion_code_update = $sql->setData($promotion_code_table, $promotion_code_update, "pay", '');
				$sql->updateRecordWhere($promotion_code_table, $promotion_code_update, $promotion_code_where);

				break;

			case 1:
				$table = "pay";
				$conf_datetime = time();
				$update = $sql->setData($table, null, "conf_datetime", $conf_datetime);
				$sql->updateRecord($table, $update, $rec["id"]);

				$table = "pay_item";
				$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
				$update = $sql->setData($table, null, "state", 1);
				$sql->updateRecordWhere($table, $update, $where);

				$table = "pay_option";
				$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
				$update = $sql->setData($table, null, "state", 1);
				$sql->updateRecordWhere($table, $update, $where);

				if($rec["pay_type"] != 'cod' && $rec["pay_type"] != "after2" && $rec["pay_type"] != 'ponpare'){
					// 購入者にもメール送信
					$rec["conf_datetime"] = $conf_datetime;
					sendmail_purchaser($rec, "conf_pay");
                }

				break;

			default:
				return false;
		}

        syncChildrenPayState($id);

		return true;
	}

	function sendmail_desingenq($rec, $template_name)
	{
		global $sql;

		$s_rec = SystemUtil::getSystemParam();

		$tmp_rec = $sql->selectRecord("user", $rec["user"]);
		if ($tmp_rec)
		{
			mail_templateFunc::sendMail("user", $template_name, $tmp_rec["mail"], $rec);
		}
	}

	function sendmail_purchaser($rec, $template_name, $user_wish_list = null)
	{
		global $sql;

        $tmp_rec = [];
        $label = 'user';

		if (empty($rec['design_store_id'])) {
			if (empty($user_wish_list)) {
				$tmp_rec = $sql->selectRecord("user", $rec["user"]);
			} else {
				$tmp_rec = $sql->selectRecord("user", $user_wish_list);
			}
		} else {
			$tmp_rec['mail'] = $rec['mail'];
            $label = 'shop_user';
            $shop_rec = $sql->selectRecord('personal_shop_info',$rec['design_store_id']);
            mail_templateFunc::sendMail($label,$template_name,$shop_rec['shop_contact'],$rec);
		}

		if (!empty($tmp_rec))
		{
			mail_templateFunc::sendMail($label, $template_name, $tmp_rec["mail"], $rec);
		}
	}

	function exportToPrintty($ids, $state, $is_kanazawa = '')
	{
		global $sql;

		$table = "pay";
		for ($i = 0; $i < count($ids); $i++) {
            if ($i > 0 && $ids[$i] == $ids[$i - 1]){
                continue;
            }
            if(!$rec = $sql->selectRecord($table, $ids[$i])) return false;

            $update = $sql->setData($table, null, "print_datetime", time());
            $update = $sql->setData($table, $update, "is_kanazawa", trim($is_kanazawa));

            // FIXME It must be delete in the final
            // $update = $sql->setData($table, $update, "printty_export", "exported");
            $sql->updateRecord($table, $update, $rec["id"]);

            $where = $sql->setWhere('pay_item','','pay','=',$ids[$i]);
            $items = $sql->getSelectResult('pay_item',$where);

            switch ($rec['is_parent'])
            {
                case 1 : //親注文
                    // 親注文はPrinttyにアップロードしない
                    break;
                case 0 :
                    while ($rec1 = $sql->sql_fetch_assoc($items)){
                        $item = $sql->selectRecord('item',$rec1['item']);
                        $item_name = $sql->selectRecord('master_item_type', $item['item_type']);
                        $rec['item_name'] = $item_name['name'];

                        if(!empty($rec['parent_pay_id'])) { //子注文
                            if(!$pay_parent = $sql->selectRecord($table, $rec['parent_pay_id'])) return false;
                            if(empty($rec['factory_id'])) {
                                $rec['pay_num'] = $pay_parent['pay_num'];
                                if ($state == 1)
                                {
                                    if (!empty($rec['user_wish_list'])) {
                                        if(!$user = $sql->selectRecord('user', $rec['user'])) return false;
                                        $rec['name'] = $user['name'];
                                        $rec['company'] = $user['company'];
                                    }
                                    sendmail_purchaser($rec, "order_printty");
                                }
                            }
                        } else if ($state == 1) {
                            if (!empty($rec['user_wish_list'])) {
                                if(!$user = $sql->selectRecord('user', $rec['user'])) return false;
                                $rec['name'] = $user['name'];
                                $rec['company'] = $user['company'];
                            }
                            sendmail_purchaser($rec, "order_printty");
                        }
                    }
                    break;
                default:
                    if ($state == 1) {
                        while ($rec1 = $sql->sql_fetch_assoc($items)) {
                            $item = $sql->selectRecord('item', $rec1['item']);
                            $item_name = $sql->selectRecord('master_item_type', $item['item_type']);
                            $rec['item_name'] = $item_name['name'];

                            if (!empty($rec['user_wish_list'])) {
                                if (!$user = $sql->selectRecord('user', $rec['user'])) return false;
                                $rec['name'] = $user['name'];
                                $rec['company'] = $user['company'];
                            }
                            sendmail_purchaser($rec, "order_printty");
                        }
                    }
            }
		}

		return true;
	}

	function changePayDelivery($id, $state, $exception = false, $merge2order = null,$tracking_number=null,$delivery_service=null)
	{
		global $sql;

		$table = "pay";
		if(!$rec = $sql->selectRecord($table, $id)) return;
        if($state == '1' && $rec['is_parent']) {
            $where = $sql->setWhere($table,null,'parent_pay_id','=',$id);
            $list_children_order = $sql->getSelectResult($table,$where);
            while ($children_pay = $sql->sql_fetch_assoc($list_children_order)) {
                if($children_pay['delivery_state'] == 0) return;
            }
        }
		$delivery_state_flag = false;

		if($exception)
		{
			$delivery_state_flag = true;
		}
		else if($rec["delivery_state"] != $state)
		{
			$delivery_state_flag = true;
		}

		if($delivery_state_flag)
		{
			if($rec["pay_type"] == "after")
			{
				$al_table = "after_log";
				$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"]);

				if($state == 1)
				{
					$data = gmoFunc::sendShip($al_rec["gmo_transaction_id"], $al_rec["slipno"], $al_rec["pdcompanycode"]);
				}

				if($state == 2)
				{
					$data = gmoFunc::sendShipCancel($al_rec["gmo_transaction_id"], $al_rec["slipno"], $al_rec["pdcompanycode"]);
				}

				if($state == 1 || $state == 2)
				{
					if(!is_object($data)) return;
					$data_array = obj2arr($data);

					$update = null;
					switch ($data_array["result"])
					{
						case 'OK':
							if($state == 2)
							{
								$update = $sql->setData($al_table, $update, "state", 5);
							}
							else
							{
								$update = $sql->setData($al_table, $update, "state", 4);
							}
							$sql->updateRecord($al_table, $update, $al_rec["id"]);
							break;
						case 'NG':
							$update = $sql->setData($al_table, $update, "state", 3);
							$error = $data_array["errors"]["error"]["errorMessage"];
							$update = $sql->setData($al_table, $update, "error_massage", $error);
							$sql->updateRecord($al_table, $update, $al_rec["id"]);
							return;
							break;
					}
				}
			}


			if($rec["pay_type"] == "after2")
			{
				$al_table = "after_log2";
				$al_rec = $sql->keySelectRecord($al_table, "pay_id", $rec["id"]);

				if($state == 1)
				{
					$url = "https://www.atobarai.jp/api/shipping/rest";

					$postdata = array();
					$postdata["EnterpriseId"]=urlencode("29182");
					$postdata["ApiUserId"]=urlencode("591");
					$postdata["OrderId"]=urlencode($al_rec["systemOrderId"]);
					if($rec["delivery_service"]=="sagawa_marui"){
						$postdata["DelivId"]=urlencode(2);//佐川

					}else{
						$postdata["DelivId"]=urlencode(1);//ヤマト
					}
					$postdata["JournalNum"]=($al_rec["slipno"]);
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
					$result = curl_exec($ch);
					curl_close($ch);
				}

				if($state == 2)
				{
					$url = "https://www.atobarai.jp/api/cancel/rest";

					$postdata = array();
					$postdata["EnterpriseId"]=urlencode("29182");
					$postdata["ApiUserId"]=urlencode("591");
					$postdata["OrderId[]"]=urlencode($al_rec["systemOrderId"]);
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
					$result = curl_exec($ch);
					curl_close($ch);
				}

				if($state == 1 || $state == 2)
				{
					if($state == 2)
					{
						$update = $sql->setData($al_table, $update, "state", 5);

						$p_table = "pay";
						$p_update = $sql->setData($p_table, null, "pay_state", 2);
						$sql->updateRecord($p_table, $p_update, $rec["id"]);

						$pi_table = "pay_item";
						$pi_where = $sql->setWhere($pi_table, null, "pay", "=", $rec["id"]);
						$pi_update = $sql->setData($pi_table, null, "state", 0);
						$sql->updateRecordWhere($pi_table, $pi_update, $pi_where);

						$po_table = "pay_option";
						$po_where = $sql->setWhere($po_table, null, "pay", "=", $rec["id"]);
						$po_update = $sql->setData($po_table, null, "state", 0);
						$sql->updateRecordWhere($po_table, $po_update, $po_where);

						//Return point to user that has using point
                        if (empty($rec['design_store_id'])) {
                            changePointLog($rec["id"], $rec["user"], 0);
                        }
					}
					else
					{
						$p_table = "pay";
						$p_update = $sql->setData($p_table, null, "pay_state", 1);
						$sql->updateRecord($p_table, $p_update, $rec["id"]);

						$pay_item_table = "pay_item";
						$where = $sql->setWhere($pay_item_table, null, "pay", "=", $rec["id"]);
						$pi_update = $sql->setData($pay_item_table, null, "state", 1);
						$sql->updateRecordWhere($pay_item_table, $pi_update, $where);

						$update = $sql->setData($al_table, $update, "state", 4);
					}
					$sql->updateRecord($al_table, $update, $al_rec["id"]);
				}
			}

			$update = $sql->setData($table, null, "delivery_state", $state);
			if (!empty($tracking_number) && !empty($delivery_service)) {
				$update = $sql->setData($table, $update, "tracking_number", $tracking_number);
				$update = $sql->setData($table, $update, "delivery_service", $delivery_service);
			}
			$update = $sql->setData($table, $update, "send_datetime", time());
			if(!is_null($merge2order)) {
				$update = $sql->setData($table, $update, 'pay_state', $state);
			}
			if (!$exception || $rec["delivery_state"] != $state) {
				$sql->updateRecord($table, $update, $rec["id"]);
			}

            if($state == '1' && !empty($rec['parent_pay_id'])) {
                $pay_children_rec = $sql->selectRecord($table,$id);
                $pay_rec = $sql->selectRecord($table,$pay_children_rec['parent_pay_id']);
                $where = $sql->setWhere($table,null,'parent_pay_id','=',$pay_rec['id']);
                $all_completed = true;
                $list_pay_children = $sql->getSelectResult($table,$where);

                while ($pay_children = $sql->sql_fetch_assoc($list_pay_children)) {
                    if($pay_children['delivery_state'] == 0) $all_completed = false;
                }

                if($all_completed) {
                    changePayDelivery($pay_rec['id'],$state);
                }
            }

			//ポイント連動
			switch($state)
			{
				case '1':
                    if (empty($rec['design_store_id'])) {
                        changePointLog($rec["id"], $rec["user"], 1);
                    }

					//#928 Check if designer item
					$table = "pay_item";
					$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
					$order = $sql->setOrder($table, null, "regist_unix", "ASC");
					$result = $sql->getSelectResult($table, $where, $order);

					while($tmp_rec = $sql->sql_fetch_assoc($result))
					{
						if($tmp_rec["item"])
						{
							//#928: Send mail reward to user.
							if($tmp2_rec = $sql->selectRecord("item", $tmp_rec["item"]))
							{
								if($tmp2_rec["user"] && $tmp2_rec["user"] != $tmp_rec["buy_user"])
								{
									if($tmp3_rec = $sql->selectRecord("user", $tmp2_rec["user"]))
									{
                                        if (!empty($rec['design_store_id'])) {
                                            $type = 'shop_owner';
                                            $tmp_rec['design_store_id'] = $rec['design_store_id'];
                                            if (!isset($shop)) {
                                                $shop = $sql->selectRecord('personal_shop_info', $rec['design_store_id']);
                                            }

                                            if (!empty($shop)) {
                                            	$tmp_rec['shop_name'] = $shop['shop_name'];
                                            } else {
                                                $tmp_rec['shop_name'] = $rec['order_from_app'];
                                            }
                                        } else {
                                            $type = 'user';
                                        }

                                        mail_templateFunc::sendMail($type, "item_pay",  $tmp3_rec["mail"], $tmp_rec);
									}
								}
							}
						}
					}
					break;

				default:
                    if (empty($rec['design_store_id'])) {
                        changePointLog($rec["id"], $rec["user"], 0);
                    }
					break;
			}

			if ($state == 1 && !$exception)
			{
                switch ($rec['is_parent']) {
                    case 1 :
                        break;
                    case 0 :
                        if(!empty($rec['parent_pay_id'])) { //子注文
                            $parent_pay = $sql->selectRecord('pay',$rec['parent_pay_id']);
                            $rec['pay_num'] = $parent_pay['pay_num'];
                        }
                        break;
                    default:
                        break;
                }

                if(!empty($rec['user_wish_list'])){
                    sendmail_purchaser($rec, "send_comp", $rec['user_wish_list']);

                    $rec['user_wish_list'] = $rec['name'];
                    if ($user = $sql->selectRecord('user', $rec['user'])) {
                        $rec['company'] = $user['company'];
                        $rec['name'] = $user['name'];
                    }
                    sendmail_purchaser($rec, "send_item_shipped", $rec['user']);

                } else {
                    sendmail_purchaser($rec, "send_comp");
                }
			}
		}
	}

	//印刷発注ステータス変更
	function changePirnt($id, $state)
	{
		global $sql;

		$table = "pay";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["print_state"] != $state)
		{
			$update = $sql->setData($table, null, "print_state", $state);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	//ガーメント発注ステータス変更
	function changeGarment($id, $state)
	{
		global $sql;

		$table = "pay";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["garment_state"] != $state)
		{
			$update = $sql->setData($table, null, "garment_state", $state);

			if($state)
				$update = $sql->setData($table, $update, "garment_unix", time());
			else
				$update = $sql->setData($table, $update, "garment_unix", 0);

			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	//オーナーステータス変更
	function changeOwnerState($id, $state)
	{
		global $sql;

		$table = "item";

		if(!$rec = $sql->selectRecord($table, $id)) return;
		if($rec["owner"] != Globals::session("LOGIN_ID")) return;

		if($rec["2nd_owner_state"] != $state)
		{
			$update = $sql->setData($table, null, "2nd_owner_state", $state);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

	//マスターステータス変更
	function changeMasterState($table, $id, $state)
	{
		global $sql;
		$order = 100000;

		if(!$rec = $sql->selectRecord($table, $id)) return;

		if($rec["state"] != $state)
		{
			$update = $sql->setData($table, null, "state", $state);
			if($state == 2){
				$update = $sql->setData($table, $update, "order", $order + $rec['order']);
			}elseif ($state == 1){
				$update = $sql->setData($table, $update, "order",$rec['order'] - $order);
			}
			$sql->updateRecord($table, $update, $rec["id"]);
		}
	}

    function changeFaqItemPageState($table, $id, $state){
        global $sql;

        if(!$rec = $sql->selectRecord($table, $id)) return;

        if($rec["item_page_state"] != $state)
        {
            $update = $sql->setData($table, null, "item_page_state", $state);
            $sql->updateRecord($table, $update, $rec["id"]);
        }
    }

    function changeCategoryState($table, $id, $state){
        global $sql;

        if(!$rec = $sql->selectRecord($table, $id)) return;

        if($rec["is_deleted"] != $state)
        {
            $update = $sql->setData($table, null, "is_deleted", $state);
            $sql->updateRecord($table, $update, $rec["id"]);
        }
    }

	function printReceipts($list, $type= null)
	{
		// FIXME depends environment
		$baseurl = "http://ondemand.cbox.nu/";
		$pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
		if (PHP_OS == "WINNT")
		{
			$baseurl = "http://localhost/uptjp/";
			$pdf = new Pdf('C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe');
		}

		$html = false;
		$body = array();
		$isStore = array();
		$sysdate = time();

		$list = explode("/", $list);
		for($i = 0; $i < count($list); $i++)
		{
			$eof = ($i == (count($list) - 1));
			$result = makeReceipts($list[$i], $eof, $html, $sysdate, $type);
			if(is_null($result)) {
				continue;
			}
			$body[] = $result['temp'];
			$isStore[] = $result['isStore'];
		}

		$smarty = new Smarty();
        $smarty->assign("isPreview", false);
		$smarty->assign("baseurl", $baseurl);
		$smarty->assign("body", implode($body));
		$smarty->assign("isStore", implode($isStore));
		$content = $smarty->fetch("template/pdf/receipt.tpl");

		if ($html)
		{
			echo $content;
		} else {

			$pdf->setOption('encoding', 'utf-8');
			$pdf->setOption('margin-top', '15mm');
			$pdf->setOption('margin-bottom', '15mm');
			$pdf->setOption('zoom', '0.75');
			//$pdf->setOption('page-size', 'A4');
			// $pdf->setOption('page-height', '100');
			// $pdf->setOption('page-width', '100');
			header("Content-Type: application/pdf");

			echo $pdf->getOutputFromHtml($content);
		}

		return $content;
	}

	function printPreviewReceipts($rec)
	{
		global $sql;
		// FIXME depends environment
		$baseurl = "http://ondemand.cbox.nu/";
		$pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
		if (PHP_OS == "WINNT")
		{
			$baseurl = "http://localhost/uptjp/";
			$pdf = new Pdf('C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe');
		}

		$html = false;
		$body = array();
        $note_ids = array();
        $counter = 1;

		foreach (Globals::session('CART_ITEM') as $cart) {
            $query = "SELECT master_item_note.message,master_item_note.id
        				FROM master_item_type
						JOIN master_categories ON master_item_type.category_id = master_categories.id
						JOIN master_item_note ON master_categories.note_id = master_item_note.id
						WHERE master_item_type.id = '{$cart['item_type']}'
						LIMIT 1";
            $master_item_note = $sql->sql_fetch_assoc($sql->rawQuery($query));
            $cart['message'] = '';

            if ($master_item_note) {
                if (!isset($note_ids[$master_item_note['id']])) {
                    $cart['message'] = $master_item_note['message'];
                    $note_ids[$master_item_note['id']] = $master_item_note['id'];
                }
            }
			if(!empty($cart['product_type']) && $cart['product_type'] == RINGPASRAL){
				$cart['message'] = "<div>【アクセサリー商品について】</div>
									 <div>■ ケアラベル・品質について<br>
										<ul>
											<li>石の種類によっては、少し落としただけで割れることがございます。</li>
											<li>アクセサリーを掃除するときは、水洗いをせずに乾いた布で汚れをふき取ってください。</li>
											<li>万が一、着用後に、肌のかゆみ、湿疹、その他アレルギー等の症状がでた場合にすぐに着用をおやめください。</li>
											<li>モニターとの色・表現方法が異なるため、色の相違が起こることがございます。</li>
										</ul>
									 </div>";
			}

            $items = [];
            $j = 0;
			foreach ($cart['item_type_size_detail'] as $val) {
				if ($j > 0) {
                    $cart['message'] = '';
				}
				$item = $sql->selectRecord('master_item_type', $cart['item_type']);
				if(!empty($cart['item_id'])) {
					$item_id = $cart['item_id'];
				} else {
                    $item_id = $counter;
                    $counter += 1;
				}

                if (!empty($rec['design_store_id'])) {
                    if (empty($items[$item_id])) {
                        $items[$item_id] = $sql->selectRecord('item', $item_id);
                    }

                    if (!empty($items[$item_id])) {
                        $item['name'] = $items[$item_id]['name'];
                    }
                }

				$size = $sql->selectRecord('master_item_type_size', $val['item_type_size']);
				$color = $sql->selectRecord('master_item_type_sub', $cart['item_type_sub']);
                $rec['item'][$item_id.'_'.$j]['master_item_type_size'] = $size['name'];
                $rec['item'][$item_id.'_'.$j]['master_item_type_sub'] = $color['name'];
                $rec['item'][$item_id.'_'.$j]['master_item_type'] = $item['name'];
                $rec['item'][$item_id.'_'.$j]['item_row'] = $val['total'];
				$rec['item'][$item_id.'_'.$j]['message'] = $cart['message'];
				$rec['item'][$item_id.'_'.$j]['product_type'] = !empty($cart['product_type']) ? $cart['product_type'] : null;
				$rec['item'][$item_id.'_'.$j]['ring_name'] = !empty($cart['ring_name']) ? $cart['ring_name'] : null;
				$rec['item'][$item_id.'_'.$j]['ring_type'] = !empty($cart['ring_type']) ? $cart['ring_type'] : null;
				$rec['item'][$item_id.'_'.$j]['ring_size'] = !empty($cart['ring_size']) ? $cart['ring_size'] : null;
				$rec['item'][$item_id.'_'.$j]['ring_color'] = !empty($cart['ring_color']) ? $cart['ring_color'] : null;
                $j++;
			}
		}

		$smarty = new Smarty();
		$smarty->assign("rec", $rec);
//		$order_date = date("Y年m月d日", mktime(0, 0, 0, '10', '12', '2018'));
		$smarty->assign("order_date", date("Y年m月d日", time()));
		$smarty->assign("sys_date", date("Y年m月d日", time()));
		$smarty->assign("tmp_list", $rec['item']);
		$smarty->assign("eof", $rec);
		$smarty->assign("html", $html);
        $smarty->assign("isStore", true);
        $smarty->assign("isPreview", true);

        if (!empty($rec['design_store_id'])) {
            $rec['shop'] = getShopInfo($rec['design_store_id']);
            $smarty->assign("rec", $rec);

            $body[] = $smarty->fetch("template/pdf/shop_receipt_body.tpl");
        } else {
            $body[] = $smarty->fetch("template/pdf/receipt_body.tpl");
        }

		$smarty = new Smarty();
        $smarty->assign("isStore", true);
        $smarty->assign("isPreview", true);
		$smarty->assign("baseurl", $baseurl);
		$smarty->assign("body", implode($body));
		$content = $smarty->fetch("template/pdf/receipt.tpl");

		if ($html)
		{
			echo $content;
		} else {
			$pdf->setOption('encoding', 'utf-8');
			$pdf->setOption('margin-top', '15mm');
			$pdf->setOption('margin-bottom', '15mm');
			$pdf->setOption('zoom', '0.75');
			header("Content-Type: application/pdf");
			echo $pdf->getOutputFromHtml($content);
		}

		return $content;
	}

	function downloadReceiptPdf($data, $preview = null) {
		global $sql;
		// FIXME depends environment
		$baseurl = "http://ondemand.cbox.nu/";
		$pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
		if (PHP_OS == "WINNT")
		{
			$baseurl = "http://localhost/uptjp/";
			$pdf = new Pdf('C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe');
		}

		$html = false;
		$body = array();

		$where = $sql->setWhere('pay_item', '', 'pay', '=', $data['id']);
		$payItem = $sql->getSelectResult('pay_item', $where);
		foreach ($payItem as $val) {
			$item = $sql->selectRecord('item', $val['item']);
			$size = $sql->selectRecord('master_item_type_size', $val['item_type_size']);
			$color = $sql->selectRecord('master_item_type_sub', $val['item_type_sub']);
			$list['item'][$val['item_id']]['size'] = $size['name'];
			$list['item'][$val['item_id']]['color'] = $color['name'];
			$list['item'][$val['item_id']]['name'] = $item['name'];
			$list['item'][$val['item_id']]['total'] = $val['item_row'];
		}

		$smarty = new Smarty();
		$smarty->assign("rec", $list);
		$order_date = date("Y年m月d日", mktime(0, 0, 0, $list["date_m"], $list["date_d"], $list["date_y"]));
		$smarty->assign("order_date", $order_date);
		$smarty->assign("sys_date", date("Y年m月d日", time()));
		$smarty->assign("tmp_list", $list['item']);
		$smarty->assign("eof", $data);
		$smarty->assign("html", $html);

		$body[] = $smarty->fetch("template/pdf/receipt_preview_body.tpl");

		$smarty = new Smarty();
		$smarty->assign("baseurl", $baseurl);
		$smarty->assign("body", implode($body));
		$content = $smarty->fetch("template/pdf/receipt_preview.tpl");

		if ($html)
		{
			echo $content;
		} else {
			$pdf->setOption('encoding', 'utf-8');
			$pdf->setOption('margin-top', '15mm');
			$pdf->setOption('margin-bottom', '15mm');
			$pdf->setOption('zoom', '0.75');
			header("Content-Type: application/pdf");
			if(!is_null($preview)) {
				echo $pdf->getOutputFromHtml($content);
				return 0;
			}
			return $pdf->getOutputFromHtml($content);
		}
	}

	function makeReceipts($id, $eof, $html, $sysdate, $type)
	{
		global $sql;

		$table = "pay";
		if(!$rec = $sql->selectRecord($table, $id)) return;

        if($rec['parent_pay_id'] && $parent_pay_rec = $sql->selectRecord($table, $rec['parent_pay_id'])){
            $rec['parent_pay_num'] = $parent_pay_rec['pay_num'];
        }


        $update = $sql->setData($table, null, "receipt_datetime", $sysdate);
		$sql->updateRecord($table, $update, $rec["id"]);

		$table = "pay_item";
		$where = $sql->setWhere($table, null, "pay", "=", $rec["id"]);
		$result = $sql->getSelectResult($table, $where);

		$tmp_list = array();
		$listItemBlank = array();
		if($result->num_rows == 0) {
			return null;
		}
		$items = [];
		$category_ids = array();
		$note_ids = array();
		while ($tmp_rec = $sql->sql_fetch_assoc($result)) {
			if(($tmp_rec['product_type'] != 'blank' && Globals::get('product_type') == 'blank')) {
				return null;
			}
			if($tmp_rec['product_type'] == 'blank') {
				$listItemBlank[] = $tmp_rec;
				continue;
			}
			if ($tmp_rec["item"]) {
				$tmp_rec = getDetailPayItem($tmp_rec, $items, $category_ids, $note_ids);

				$tmp_list[] = $tmp_rec;
			}

		}
		if($listItemBlank) {
			foreach ($listItemBlank as $tmp_rec) {
				if ($tmp_rec["item"]) {
					$tmp_rec = getDetailPayItem($tmp_rec, $items, $category_ids, $note_ids);

					$tmp_list[] = $tmp_rec;
				}
			}
		}

        if(!empty($rec["send_datetime"])){
        $timestamp_confdatetime = strtotime($rec["send_datetime"]);
        }
        else{
            $timestamp_confdatetime = time();
        }
		$smarty = new Smarty();
		$smarty->assign("rec", $rec);
		$order_date = date("Y年m月d日", mktime(0, 0, 0, $rec["date_m"], $rec["date_d"], $rec["date_y"]));
		$smarty->assign("order_date", $order_date);
		$smarty->assign("sys_date", date("Y年m月d日", $timestamp_confdatetime));
		$smarty->assign("tmp_list", $tmp_list);
		$smarty->assign("eof", $eof);
		$smarty->assign("html", $html);
        $smarty->assign("isPreview", false);

        if($type == 'user') {
            $smarty->assign("isStore", false);
        } elseif (!empty($rec['store_represent_name']) && empty($rec['design_store_id'])) {
			$smarty->assign("isStore", true);
		} else {
			$smarty->assign("isStore", false);
		}

		$body = array();

		if($type=="user"){
			if (!empty($rec['design_store_id']) && $shop = $sql->selectRecord('personal_shop_info', $rec['design_store_id'])) {
                $smarty->assign("shop", $shop);
                $body['temp'] = $smarty->fetch("template/pdf/design_store_user.tpl");
			} else {
                $body['temp'] = $smarty->fetch("template/pdf/receipt_user.tpl");
			}
		} else{
            if (!empty($rec['design_store_id'])) {
                $rec['shop'] = getShopInfo($rec['design_store_id']);
                $smarty->assign("rec", $rec);

                $body['temp'] = $smarty->fetch("template/pdf/shop_receipt_body.tpl");
            } else {
                $body['temp'] = $smarty->fetch("template/pdf/receipt_body.tpl");
            }
		}

		if ($type == 'user') {
            $body['isStore'] = false;
		} elseif(!empty($rec['store_represent_name']) && empty($rec['design_store_id'])) {
			$body['isStore'] = true;
		} else {
			$body['isStore'] = false;
		}

		return $body;
	}

	function getDetailPayItem($tmp_rec, &$items, &$category_ids, &$note_ids) {
		global $sql;
		$query = "SELECT master_item_note.message,master_item_type.category_id,master_item_note.id
							FROM pay_item
							JOIN master_item_type ON pay_item.item_type = master_item_type.id
							JOIN master_categories ON master_item_type.category_id = master_categories.id
							JOIN master_item_note ON master_categories.note_id = master_item_note.id
							WHERE pay_item.pay = '{$tmp_rec['pay']}'
							AND pay_item.item_type = '{$tmp_rec['item_type']}'";

		$master_item_note = $sql->sql_fetch_assoc($sql->rawQuery($query));
		$tmp_rec['message'] = '';

		if(!empty($tmp_rec['product_type']) && $tmp_rec['product_type'] == RINGPASRAL){
			$tmp_rec['message'] = "<div>【アクセサリー商品について】</div>
									 <div>■ ケアラベル・品質について<br>
										<ul>
											<li>石の種類によっては、少し落としただけで割れることがございます。</li>
											<li>アクセサリーを掃除するときは、水洗いをせずに乾いた布で汚れをふき取ってください。</li>
											<li>万が一、着用後に、肌のかゆみ、湿疹、その他アレルギー等の症状がでた場合にすぐに着用をおやめください。</li>
											<li>モニターとの色・表現方法が異なるため、色の相違が起こることがございます。</li>
										</ul>
									 </div>";
		}
		if ($master_item_note) {
			if (!isset($category_ids[$master_item_note['category_id']]) && !isset($note_ids[$master_item_note['id']])) {
				$tmp_rec['message'] = $master_item_note['message'];
				$category_ids[$master_item_note['category_id']] = $master_item_note['category_id'];
				$note_ids[$master_item_note['id']] = $master_item_note['id'];
			}
		}

		// 商品種別・サイズ・カラーを取得
		if ($tmp2_rec = $sql->selectRecord("master_item_type", $tmp_rec["item_type"])) {
			if (!empty($rec['design_store_id'])) {
				if (empty($items[$tmp_rec['item']])) {
					$items[$tmp_rec['item']] = $sql->selectRecord('item', $tmp_rec['item']);
				}

				if (!empty($items[$tmp_rec['item']])) {
					$tmp2_rec['name'] = $items[$tmp_rec['item']]['name'];
				}
			}

			$tmp_rec["master_item_type"] = $tmp2_rec["name"];
			$tmp_rec["item_code"] = $tmp2_rec["item_code"];

		};
		if($tmp2_rec = $sql->selectRecord("master_item_type_size", $tmp_rec["item_type_size"])) $tmp_rec["master_item_type_size"] = $tmp2_rec["name"];
		if($tmp2_rec = $sql->selectRecord("master_item_type_sub", $tmp_rec["item_type_sub"])) $tmp_rec["master_item_type_sub"] = $tmp2_rec["name"];

		// 表・裏・左袖・右袖の有無を取得
		$print_sides = array();
		if($i_rec = $sql->selectRecord("item", $tmp_rec["item"]))
		{
			if (!empty($i_rec["item_preview1"])) $print_sides[] = "表";
			if (!empty($i_rec["item_preview2"])) $print_sides[] = "裏";
			if (!empty($i_rec["item_preview3"])) $print_sides[] = "左袖";;
			if (!empty($i_rec["item_preview4"])) $print_sides[] = "右袖";
		}
		$tmp_rec["print_sides"] = implode("・", $print_sides);

		return $tmp_rec;
	}

	//カート商品登録
	function addPayItem($pay_rec, $cart_rec, $item_rec, &$seq_number = false)
	{
		global $sql;

		$per = 1;	//報酬加算倍数
		if($u_rec = $sql->selectRecord("user", $item_rec["user"]))
		{
			if($u_rec["premium_flag"]) $per = 1.2;
		}

		$it_rec = $sql->selectRecord("master_item_type", $item_rec["item_type"]);
		$itsu_rec = $sql->selectRecord("master_item_type_sub", $cart_rec["item_type_sub"]);

		$item_price = $it_rec["item_price"];

		if($item_rec['product_type'] == 'bl') {
			$item_price = $cart_rec["cart_price"];
		}
		if(!empty($pay_rec['is_pasral']) && $pay_rec['is_pasral'] == 1) {
			$item_price = $cart_rec["item_price"];
		}

//		global $FREE_DESIGN_ITEM_ID;
//		$tmp_rec3 = $sql->selectRecord("item", $item_rec["id"]);
//		if (($item_rec["id"] != $FREE_DESIGN_ITEM_ID) && ($tmp_rec3["owner_item"] != $FREE_DESIGN_ITEM_ID))
//		{
        if (!empty($item_rec['embroidery_print'])) {
            $cart_data = [];
            list($cart_data, $item_price) = updateCartValue($cart_data, $item_rec, $itsu_rec, $item_price);
        } else {
            if($item_rec["item_image1"]) $item_price += $itsu_rec["cost1"];
            if($item_rec["item_image2"]) $item_price += $itsu_rec["cost2"];
            if($item_rec["item_image3"]) $item_price += $itsu_rec["cost3"];
            if($item_rec["item_image4"]) $item_price += $itsu_rec["cost3"];
        }
//		}
//		else
//		{
//			if($cart_rec["front_design_type"]) $item_price += $itsu_rec["cost1"];
//			if($cart_rec["back_design_type"]) $item_price += $itsu_rec["cost2"];
//			if($cart_rec["left_design_type"]) $item_price += $itsu_rec["cost3"];
//			if($cart_rec["right_design_type"]) $item_price += $itsu_rec["cost3"];
//		}
        $item_rec["fee_option"] = 0;
		$price = $item_price + $item_rec["fee_owner"] + $item_rec["fee_user"] + $item_rec["fee_option"];
		$discount_niko2=1;
		if($cart_rec['design_from']=='niko2'){
			$discount_niko2=0.8;
		}
		$tmp_table = "pay_item";
		foreach ($cart_rec['item_type_size_detail'] as $key => $val) {
			$tmp_rec = $sql->setData($tmp_table, null, "id", SystemUtil::getUniqId($tmp_table, false, true));
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item", $item_rec["id"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "owner", $item_rec["owner"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "user", $item_rec["user"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "buy_user", $pay_rec["user"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "pay", $pay_rec["id"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_image", $cart_rec["image_preview1"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_name", $item_rec["name"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_type", $cart_rec["item_type"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_type_sub", $cart_rec["item_type_sub"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_type_size", $val["item_type_size"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_row", $val["total"]);
            if(!empty($pay_rec['is_pasral']) && $pay_rec['is_pasral'] == 1) {
            	if($item_rec["user"] == Globals::session("LOGIN_ID")){
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_price", $item_price);
				}
				else{
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_price", $price);
				}
			}
			else{
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_price", $item_price);
			}
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", round($item_rec["fee_owner"] / 100 * (50 * $per)) * $val["total"]);	//二次利用報酬は50%（プレミアムは60%）
			if($cart_rec["item_type"]=='IT303' || $cart_rec["item_type"]=='IT304'
				|| $cart_rec["item_type"]=='IT305'  || $cart_rec["item_type"]=='IT306'
				|| $cart_rec["item_type"]=='IT307'  || $cart_rec["item_type"]=='IT309'){

				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "side_chinchi_fee", isset($cart_rec["side_chinchi"]['fee']) ? $cart_rec["side_chinchi"]['fee'] : 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "side_chinchi_text", isset($cart_rec["side_chinchi"]['text']) ? $cart_rec["side_chinchi"]['text'] : null);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "upper_tip_fee", isset($cart_rec["upper_tip"]['fee']) ? $cart_rec["upper_tip"]['fee'] : 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "upper_tip_text", isset($cart_rec["upper_tip"]['text']) ? $cart_rec["upper_tip"]['text'] : null);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "chichi_color_fee", isset($cart_rec["chichi_color"]['fee']) ? $cart_rec["chichi_color"]['fee'] : 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "chichi_color_text", isset($cart_rec["chichi_color"]['text']) ? $cart_rec["chichi_color"]['text'] : null);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "deformation_cut_fee", isset($cart_rec["deformation_cut"]['fee']) ? $cart_rec["deformation_cut"]['fee'] : 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "deformation_cut_text", isset($cart_rec["deformation_cut"]['text']) ? $cart_rec["deformation_cut"]['text'] : null);

		}
		if(($item_rec["owner_item"]==null || $item_rec["owner_item"]== '') && Globals::session("LOGIN_ID") && (Globals::session("LOGIN_ID")== $item_rec["user"]|| Globals::session("LOGIN_ID")== $item_rec["owner"]))
		{
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", 0);	//セルフは報酬0
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_option", 0);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "s_price", ($price - $item_rec["fee_user"])*$discount_niko2);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "t_price", (($price - $item_rec["fee_user"]) * $val['total'])*$discount_niko2);
		}elseif (($item_rec["owner_item"]!=null || $item_rec["owner_item"]!= '')&&  $tmp_rec["buy_user"] == $tmp_rec["owner"]){
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", 0);	//セルフは報酬0
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", 0);
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_option", 0);
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "s_price", ($price - $item_rec["fee_owner"])*$discount_niko2);
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "t_price", (($price - $item_rec["fee_owner"]) * $val['total'])*$discount_niko2);
        } elseif (($item_rec["owner_item"]!=null || $item_rec["owner_item"]!= '')&&  $tmp_rec["buy_user"] == $tmp_rec["user"]){
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", 0);	//セルフは報酬0
            if($item_rec['fee_owner'] > 0){
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", $tmp_rec['fee_owner']);
            }else{
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", 0);
            }
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_option", 0);
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "s_price", ($price - $item_rec["fee_user"])*$discount_niko2);
            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "t_price", (($price - $item_rec["fee_user"]) * $val['total'])*$discount_niko2);
        }else {

				if(!empty($item_rec["user"])){
                    $userDesignItem = $sql->selectRecord('user', $item_rec["user"]);
                    $system_usage_fee = 10;
                    if(!empty($userDesignItem["system_usage_fee"]) || $userDesignItem["system_usage_fee"]== '0'){
                        $system_usage_fee = $userDesignItem["system_usage_fee"];
					}
				}
            if(($tmp_rec["owner"]!=null || $tmp_rec["owner"]!= '') && $tmp_rec["user"] != $tmp_rec["buy_user"]){

                $table_item = "item";
                $where_tam = $sql->setWhere($table_item, null, "id", "=", $item_rec["owner_item"]);
                $result_tam = $sql->getSelectResult($table_item, $where_tam);
                while($tmp_rec_old = $sql->sql_fetch_assoc($result_tam))
                {
                    $fee_user_old = $tmp_rec_old["fee_user"];
                    $nd_margin_state_old = $tmp_rec_old["2nd_margin_state"];
                }
                if($nd_margin_state_old == 1){
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", round($fee_user_old/ 100 * ((100-$system_usage_fee) * $per)) * $val["total"]);
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", 0);
                }
                elseif ($nd_margin_state_old == 0)
                {
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", round($fee_user_old/ 100 * ((100-$system_usage_fee) * $per)) * $val["total"]);	//一次利用報酬は70%（プレミアムは84%）
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", round($item_rec["fee_user"]/ 100 * (50 * $per)) * $val["total"]);
                }
            }
            else
            {
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", round($item_rec["fee_user"] / 100 * ((100-$system_usage_fee) * $per)) * $val["total"]);	//一次利用報酬は70%（プレミアムは84%）
            }

//            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", round($item_rec["fee_user"] / 100 * (70 * $per)) * $val['total']);	//一次利用報酬は70%（プレミアムは84%）
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_option", 0);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "s_price", $price*$discount_niko2);
			if (is_mask($item_rec['id'])) {
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "t_price", get_mask_prices($val['total'])['total']);
            } else {
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "t_price", ($price * $val['total'])*$discount_niko2);
            }
		}

			$isPrintByLayersActivated = isset($cart_rec["print_by_layers_activated"]) ? $cart_rec["print_by_layers_activated"] : 0;
			$isLayersPrintAllowed = isset($cart_rec["is_layers_print_allowed"]) ? $cart_rec["is_layers_print_allowed"] : 0;
			$printByLayersPlatePrice = isset($cart_rec["print_by_layers_plate_price"]) ? $cart_rec["print_by_layers_plate_price"] : 0;
			$printByLayersItemPrice = isset($cart_rec["print_by_layers_item_price"]) ? $cart_rec["print_by_layers_item_price"] : 0;
			$printByLayersFrontPrice = isset($cart_rec["print_by_layers_front_price"]) ? $cart_rec["print_by_layers_front_price"] : 0;
			$printByLayersBackPrice = isset($cart_rec["print_by_layers_back_price"]) ? $cart_rec["print_by_layers_back_price"] : 0;
			$printByLayersLeftPrice = isset($cart_rec["print_by_layers_left_price"]) ? $cart_rec["print_by_layers_left_price"] : 0;
			$printByLayersRightPrice = isset($cart_rec["print_by_layers_right_price"]) ? $cart_rec["print_by_layers_right_price"] : 0;
			$printByLayersTotalPrice = $printByLayersPlatePrice + (($printByLayersItemPrice + $printByLayersFrontPrice +
						$printByLayersBackPrice + $printByLayersLeftPrice + $printByLayersRightPrice) * $val["total"]);

			$design_id_pasral = isset($cart_rec["design_id_pasral"]) ? $cart_rec["design_id_pasral"] : null;
			$hash_code_pasral = isset($cart_rec["hash_code_pasral"]) ? $cart_rec["hash_code_pasral"] : null;
			$number_image = isset($cart_rec["number_image"]) ? $cart_rec["number_image"] : null;
			$ring_name = isset($cart_rec["ring_name"]) ? $cart_rec["ring_name"] : null;
			$ring_type = isset($cart_rec["ring_type"]) ? $cart_rec["ring_type"] : null;
			$ring_size = isset($cart_rec["ring_size"]) ? $cart_rec["ring_size"] : null;
			$ring_color = isset($cart_rec["ring_color"]) ? $cart_rec["ring_color"] : null;

			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "is_layers_print_allowed", $isLayersPrintAllowed);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_activated", $isPrintByLayersActivated);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_plate_price", $printByLayersPlatePrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_item_price", $printByLayersItemPrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_front_price", $printByLayersFrontPrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_back_price", $printByLayersBackPrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_left_price", $printByLayersLeftPrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_right_price", $printByLayersRightPrice);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "print_by_layers_total_price", $printByLayersTotalPrice);

			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "design_id_pasral", $design_id_pasral);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "hash_code_pasral", $hash_code_pasral);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "number_image", $number_image);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_name", $ring_name);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_type", $ring_type);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_size", $ring_size);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_color", $ring_color);

			$pay_item_state = $pay_rec["pay_type"] == 'amazon_pay' ? 1 : 0;
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "state", $pay_item_state);

			$time = $pay_rec["regist_unix"];
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "date_y", date("Y", $time));
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "date_m", date("n", $time));
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "date_d", date("j", $time));
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "regist_unix", $time);

			if($seq_number !== false) {
                $seq_number++;
			}

            $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "pay_item_num", "{$pay_rec["pay_num"]}-{$seq_number}");

			if(isset($val['product_type']) && $val['product_type'] == 'blank') {
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "product_type", "blank");
			}

			if(isset($cart_rec['product_type']) && $cart_rec['product_type'] == RINGPASRAL) {
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "product_type", RINGPASRAL);
			}

            $pay_item_result = $sql->addRecord($tmp_table, $tmp_rec);

            if(empty($pay_item_result)){
				redirectToErrorPage($pay_rec["id"]);
                break;
            }
		}

/*
		global $FREE_DESIGN_ITEM_ID;
		$tmp_rec3 = $sql->selectRecord("item", $item_rec["id"]);
		if (($item_rec["id"] == $FREE_DESIGN_ITEM_ID) || ($FREE_DESIGN_ITEM_ID == $tmp_rec3["owner_item"])) {
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "front_design_type", $cart_rec["front_design_type"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "back_design_type", $cart_rec["back_design_type"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "left_design_type", $cart_rec["left_design_type"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "right_design_type", $cart_rec["right_design_type"]);
		}
*/

		//オプション同時登録
		if($cart_rec["option_data"])
		{
			foreach($cart_rec["option_data"] as $key => $val)
			{
				$tmp_table2 = "pay_option";
				$tmp_rec2 = $sql->setData($tmp_table2, null, "id", SystemUtil::getUniqId($tmp_table2, false, true));
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "user", $pay_rec["user"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "owner", $val["option_owner"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "item", $item_rec["id"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "pay", $pay_rec["id"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "option_id", $val["option_id"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "option_price", $val["option_price"] * $cart_rec["cart_row"]);
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "fee_owner", round($tmp_rec2["option_price"] / 100 * 50));	//オプション報酬は50%（プレミアムも50%）
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "state", 0);

				$time = $pay_rec["regist_unix"];
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "date_y", date("Y", $time));
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "date_m", date("n", $time));
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "date_d", date("j", $time));
				$tmp_rec2 = $sql->setData($tmp_table2, $tmp_rec2, "regist_unix", $time);

				$sql->addRecord($tmp_table2, $tmp_rec2);
			}
		}

		//販売制限在庫減処理
		if($item_rec["buy_count_state"])
		{
			$table = "item";
			if(!$item_rec = $sql->selectRecord($table, $item_rec["id"])) return;

			$buy_count_row = $item_rec["buy_count_row"] - $cart_rec["cart_row"];
			if($buy_count_row < 0) $buy_count_row = 0;

			$update = $sql->setData($table, null, "buy_count_row", $buy_count_row);
			$sql->updateRecord($table, $update, $item_rec["id"]);
		}

		return $tmp_rec;
	}

	//商品同時登録or商品データ参照
	function getCartItem($user, $cart_rec, $is_create_preview = false)
	{
		global $sql;

		if(!$user_rec = $sql->selectRecord("user", $user)) return;

		switch($cart_rec["design_type"])
		{
			case 'select':

				if(!$cart_rec["item_id"]) return;
				return $sql->selectRecord("item", $cart_rec["item_id"]);

			case 'new':
			case 'edit':

				//既に同じ商品が登録されている場合
				if($cart_rec["image_id"] && $cart_rec["design_type"] != 'new'){
                    $tmp_rec = $sql->keySelectRecord("item", "image_id", $cart_rec["image_id"]);

					if($tmp_rec) {
                        insert_item_preview($tmp_rec, $is_create_preview);

                        return $tmp_rec;
                    }
				}
				// if pasral
				if(!empty($cart_rec["design_id_pasral"]) && $cart_rec["design_type"] != 'new'){
					if($tmp_rec = $sql->keySelectRecord("item", "design_id_pasral", $cart_rec["design_id_pasral"])) return $tmp_rec;
				}

				$tmp_table = "item";
				$tmp_rec = $sql->setData($tmp_table, null, "id", SystemUtil::getUniqId($tmp_table, false, true));
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "user", $user_rec["id"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "name", "「".date("Y年n月j日 H:i")."」に作成したデザイン");
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_type", $cart_rec["item_type"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_type_sub", $cart_rec["item_type_sub"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_text", $tmp_rec["name"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "image_id", $cart_rec["image_id"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_preview1", $cart_rec["image_preview1"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_preview2", $cart_rec["image_preview2"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_preview3", $cart_rec["image_preview3"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_preview4", $cart_rec["image_preview4"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_image1", $cart_rec["image_path1"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_image2", $cart_rec["image_path2"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_image3", $cart_rec["image_path3"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_image4", $cart_rec["image_path4"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "item_price", $cart_rec["item_price"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", 0);


				if (!empty($cart_rec['product_type'])) {
                    $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "product_type", $cart_rec['product_type']);
                }

	            if (!empty($cart_rec['embroidery_print'])) {
	                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "embroidery_print", $cart_rec['embroidery_print']);
	            }

				//オプション費用計算
				$fee_option = 0;
				if($cart_rec["option_data"])
				{
					foreach($cart_rec["option_data"] as $key => $val)
					{
//						$fee_option += $val["option_price"];

						//レコード追加
						$tmp2_table = "item_option";
						$tmp2_rec = $sql->setData($tmp2_table, null, "id", SystemUtil::getUniqId($tmp2_table, false, true));
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "user", $tmp_rec["user"]);
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "owner", $val["option_owner"]);
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "item", $tmp_rec["id"]);
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "option_id", $val["option_id"]);
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "option_price", $val["option_price"]);
						$tmp2_rec = $sql->setData($tmp2_table, $tmp2_rec, "regist_unix", time());
						$sql->addRecord($tmp2_table, $tmp2_rec);
					}
				}
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_option", $fee_option);

				if($cart_rec["design_type"] == "edit" || ($cart_rec["design_type"] == "new" && !empty($cart_rec["item_id"])))
				{
					if(!$cart_rec["item_id"]) return;
					$item_rec = $sql->selectRecord("item", $cart_rec["item_id"]);

                    if(getSelfFlag($cart_rec["item_id"]) || $item_rec["user"] == Globals::session("LOGIN_ID")){
                        $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_user", $item_rec["fee_user"]);
                    } else {
                        $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", $item_rec["fee_user"]);
                        $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "owner", $item_rec["user"]);
                        $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "owner_item", $item_rec["id"]);
                    }

					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_p1", $item_rec["normal_cat_p1"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_c1", $item_rec["normal_cat_c1"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_p2", $item_rec["normal_cat_p2"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_c2", $item_rec["normal_cat_c2"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_p3", $item_rec["normal_cat_p3"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_c3", $item_rec["normal_cat_c3"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_p4", $item_rec["normal_cat_p4"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_c4", $item_rec["normal_cat_c4"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_p5", $item_rec["normal_cat_p5"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "normal_cat_c5", $item_rec["normal_cat_c5"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_p1", $item_rec["premium_cat_p1"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_c1", $item_rec["premium_cat_c1"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_p2", $item_rec["premium_cat_p2"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_c2", $item_rec["premium_cat_c2"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_p3", $item_rec["premium_cat_p3"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_cat_c3", $item_rec["premium_cat_c3"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tag1", $item_rec["tag1"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tag2", $item_rec["tag2"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tag3", $item_rec["tag3"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tag4", $item_rec["tag4"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tag5", $item_rec["tag5"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "2nd_owner_state", $item_rec["2nd_owner_check"]);

                    if ($item_rec['user'] == '605ae6b1f17dc' || !in_array($item_rec['user'], CREATOR_ID)) {
                        if ($item_rec['id'] !== $cart_rec['item_id']) {
                            $own_item = $sql->selectRecord('item', $cart_rec['item_id']);

                            if (!empty($own_item)) {
                                $item_rec['battle_time'] = $own_item['battle_time'];
                            }
                        }

                        $tmp_rec = $sql->setData($tmp_table, $tmp_rec, 'battle_time', $item_rec['battle_time']);
                    }

					//二次創作で承認制の場合はオーナーにメール送信
					if(!$tmp_rec["2nd_owner_state"] && !empty($tmp_rec['owner_item']))
					{
						if($tmp2_rec = $sql->selectRecord("user", $item_rec["user"]))
						{
							mail_templateFunc::sendMail("user", "item_owner", $tmp2_rec["mail"], $tmp_rec);
						}
					}
				}
				else
				{
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "2nd_owner_state", 1);	//一次利用の場合は無条件で許可
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", 0);
				}

				if($cart_rec['product_type'] == RINGPASRAL && isset($cart_rec['pasral_design_again']) && $cart_rec['pasral_design_again'] == 1){
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "owner_item", $cart_rec["owner_item"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "fee_owner", $cart_rec["fee_owner"]);
					$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "owner", $cart_rec["owner"]);
				}

				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "price", $tmp_rec["item_price"] + $tmp_rec["fee_user"] + $tmp_rec["fee_owner"] + $tmp_rec["fee_option"]);

				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "search_word", itemSearchWord($tmp_rec));
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "mall_state", 1);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "2nd_state", 1);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "2nd_margin_state", 1);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "2nd_owner_check", 1);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "buy_state", 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "buy_count_state", 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "buy_count_row", 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "rank_count", 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "premium_flag", $user_rec["premium_flag"]);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "pickup", 0);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "state", 1);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "edit_unix", time());
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "regist_unix", time());

                insert_item_preview($tmp_rec, $is_create_preview);

				//insert pasral
                $design_id_pasral = isset($cart_rec["design_id_pasral"]) ? $cart_rec["design_id_pasral"] : null;
                $hash_code_pasral = isset($cart_rec["hash_code_pasral"]) ? $cart_rec["hash_code_pasral"] : null;
				$number_image = isset($cart_rec["number_image"]) ? $cart_rec["number_image"] : null;
				$ring_name = isset($cart_rec["ring_name"]) ? $cart_rec["ring_name"] : null;
				$ring_type = isset($cart_rec["ring_type"]) ? $cart_rec["ring_type"] : null;
				$ring_size = isset($cart_rec["ring_size"]) ? $cart_rec["ring_size"] : null;
				$ring_color = isset($cart_rec["ring_color"]) ? $cart_rec["ring_color"] : null;

                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "design_id_pasral", $design_id_pasral);
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "hash_code_pasral", $hash_code_pasral);
                $tmp_rec = $sql->setData($tmp_table, $tmp_rec, "number_image", $number_image);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_name", $ring_name);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_type", $ring_type);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_size", $ring_size);
				$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "ring_color", $ring_color);

				$sql->addRecord($tmp_table, $tmp_rec);

				return $sql->selectRecord($tmp_table, $tmp_rec["id"]);
		}
	}

	//会員同時登録
	function addUser($rec)
	{
		global $sql;

		$tmp_table = "user";
		$tmp_rec = $sql->setData($tmp_table, null, "id", SystemUtil::getUniqId($tmp_table, false, true));
		if(!empty($rec['store_email']) && !empty($rec['store_represent_name']) && !empty($rec['store_add_num'])) {
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "company", $rec["store_shop_name"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "name", $rec["store_represent_name"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "mail", $rec["store_email"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "full_name", $rec["store_shop_name"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tel", $rec["store_phone_number"]);
			//$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "full_name_ruby", $rec["name_ruby"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_num", $rec["store_add_num"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_pre", $rec["store_add_pre"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_sub", $rec["store_add_sub"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_sub2", $rec["store_add_sub2"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_text", $rec["store_add_pre"].$rec["store_add_sub"]." ".$rec["store_add_sub2"]);
		} else {
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "company", $rec["company"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "name", $rec["name"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "mail", $rec["mail"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "full_name", $rec["name"]);
			//$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "full_name_ruby", $rec["name_ruby"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_num", $rec["add_num"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_pre", $rec["add_pre"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_sub", $rec["add_sub"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_sub2", $rec["add_sub2"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "tel", $rec["tel"]);
			$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "add_text", $tmp_rec["add_pre"].$tmp_rec["add_sub"]." ".$tmp_rec["add_sub2"]);
		}

		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "pass", TextUtil::createPassword(8));
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "bank_u_type", "ordinary");

		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "search_state", 1);
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "search_word", "");
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "state", 1);
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "login_unix", time());
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "edit_unix", time());
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "regist_unix", time());
		$tmp_rec = $sql->setData($tmp_table, $tmp_rec, "gmo_member_id", $GLOBALS['MemberID']);

		$sql->addRecord($tmp_table, $tmp_rec);

		$s_rec = SystemUtil::getSystemParam();

		//メール送信
		mail_templateFunc::sendMail("admin", $tmp_table, $s_rec["mail"], $tmp_rec);
		mail_templateFunc::sendMail("nobody", $tmp_table, $tmp_rec["mail"], $tmp_rec);

		//パスワードをsha1用に再アップデート
		$update = $sql->setData($tmp_table, null, "pass", sha1($tmp_rec["pass"]));
		$sql->updateRecord($tmp_table, $update, $tmp_rec["id"]);

		//検索ワード更新
		userSearchWord($tmp_rec["id"]);

		return $tmp_rec["id"];
	}

	//注文番号を生成
	function getPayNum()
	{
		global $sql;

		$s_rec = SystemUtil::getSystemParam();
		$date = date("ymd");

		if($s_rec["pay_num_date"] != $date)
			$num = 1;
		else
			$num = $s_rec["pay_num_count"] + 1;

		$table = "system";
		$update = $sql->setData($table, null, "pay_num_date", $date);
		$update = $sql->setData($table, $update, "pay_num_count", $num);
		$sql->updateRecord($table, $update, $s_rec["id"]);

		return $date.sprintf("%03d", $num) . rand(0,9);
	}

	//カートの金額を取得
	function getCartPrice()
	{
		$item_total = 0;

		if($cart = Globals::session("CART_ITEM"))
		{
			foreach($cart as $key => $val)
			{
                $isPrintByLayersActivated = isset($val["print_by_layers_activated"]) ? $val["print_by_layers_activated"] : '0';
                if (!$isPrintByLayersActivated) {
                    if (is_mask($val['item_id'])) {
                        $item_calc = get_mask_prices(get_mask_quantity($val))['total'];
                    } else {
                        $item_price = $val["cart_price"];
                        $item_calc = $item_price * $val["cart_row"];
                    }

                    if(isset($val["design_from"])) {
						if ($val["design_from"] == 'niko2') {
							$item_calc = $item_calc * 0.8;
						}
					}
                    $item_total += $item_calc;
				} else {
                    $printByLayersPlatePrice = isset($val["print_by_layers_plate_price"]) ? $val["print_by_layers_plate_price"] : 0;
                    $printByLayersItemPrice = isset($val["print_by_layers_item_price"]) ? $val["print_by_layers_item_price"] : 0;
                    $printByLayersFrontPrice = isset($val["print_by_layers_front_price"]) ? $val["print_by_layers_front_price"] : 0;
                    $printByLayersBackPrice = isset($val["print_by_layers_back_price"]) ? $val["print_by_layers_back_price"] : 0;
                    $printByLayersLeftPrice = isset($val["print_by_layers_left_price"]) ? $val["print_by_layers_left_price"] : 0;
                    $printByLayersRightPrice = isset($val["print_by_layers_right_price"]) ? $val["print_by_layers_right_price"] : 0;
                    $printByLayersTotalPrice = $printByLayersPlatePrice + (($printByLayersItemPrice + $printByLayersFrontPrice +
                                $printByLayersBackPrice + $printByLayersLeftPrice + $printByLayersRightPrice) * $val["cart_row"]);
					if(isset($val["design_from"])) {
						if ($val["design_from"] == 'niko2') {
							$printByLayersTotalPrice = $printByLayersTotalPrice * 0.8;
						}
					}
                    $item_total += $printByLayersTotalPrice;
				}
				if($val["item_type"]=='IT303' || $val["item_type"]=='IT304'
					|| $val["item_type"]=='IT305'  || $val["item_type"]=='IT306'
					|| $val["item_type"]=='IT307'  || $val["item_type"]=='IT309'){
					$side_chinchi_fee = isset($val["side_chinchi"]['fee']) ? $val["side_chinchi"]['fee'] : 0;
					$upper_tip_fee = isset($val["upper_tip"]['fee']) ? $val["upper_tip"]['fee'] : 0;
					$chichi_color_fee = isset($val["chichi_color"]['fee']) ? $val["chichi_color"]['fee'] : 0;
					$deformation_cut_fee = isset($val["deformation_cut"]['fee']) ? $val["deformation_cut"]['fee'] : 0;
					$discount_niko2 = 1;
					if(isset($val["design_from"])) {
						if ($val["design_from"] == 'niko2') {
							$discount_niko2 = 0.8;
						}
					}
					$item_total = $item_total + (($side_chinchi_fee + $upper_tip_fee + $chichi_color_fee + $deformation_cut_fee) * $val["cart_row"] *$discount_niko2);
				}
			}
		}
		return $item_total;
	}

	//代引手数料設定
	function getCod($pay_type)
	{
        if (is_normal_mask()) {
            return 0;
        }

		switch($pay_type)
		{
			case 'cod':
				return SystemUtil::getSystemParam("default_cod");
			default:
				return 0;
		}
	}

	//送料設定
	//$pay_totalには税抜き価格をセットする
	//プロモーションコード割引はpay_totalに含めないで割引前の価格で計算する
	function getPostage($add_pre, $pay_total, $pay_type)
	{
        if (is_normal_mask()) {
            return mask_delivery_fee();
        }

        $postage_limit = SystemUtil::getSystemParam("postage_limit");

		if($postage_limit <= $pay_total) return 0;

		//支払い方法で分ける
		$pay_type_system = SystemUtil::getSystemParam("pay_type");
		if($pay_type_system){
			$arr=explode("\t", $pay_type_system);
			if(in_array($pay_type."_pay", $arr)){
				 return 0;
			}
		}

		$add_pre_array = getAddPrePostageList();

		return SystemUtil::getSystemParam($add_pre_array[$add_pre]);
	}

	function getAddPrePostageList()
	{
		$add_pre_array = array(
			"北海道" => "P01_postage",
			"青森県" => "P02_postage",
			"岩手県" => "P03_postage",
			"宮城県" => "P04_postage",
			"秋田県" => "P05_postage",
			"山形県" => "P06_postage",
			"福島県" => "P07_postage",
			"茨城県" => "P08_postage",
			"栃木県" => "P09_postage",
			"群馬県" => "P10_postage",
			"埼玉県" => "P11_postage",
			"千葉県" => "P12_postage",
			"東京都" => "P13_postage",
			"神奈川県" => "P14_postage",
			"新潟県" => "P15_postage",
			"富山県" => "P16_postage",
			"石川県" => "P17_postage",
			"福井県" => "P18_postage",
			"山梨県" => "P19_postage",
			"長野県" => "P20_postage",
			"岐阜県" => "P21_postage",
			"静岡県" => "P22_postage",
			"愛知県" => "P23_postage",
			"三重県" => "P24_postage",
			"滋賀県" => "P25_postage",
			"京都府" => "P26_postage",
			"大阪府" => "P27_postage",
			"兵庫県" => "P28_postage",
			"奈良県" => "P29_postage",
			"和歌山県" => "P30_postage",
			"鳥取県" => "P31_postage",
			"島根県" => "P32_postage",
			"岡山県" => "P33_postage",
			"広島県" => "P34_postage",
			"山口県" => "P35_postage",
			"徳島県" => "P36_postage",
			"香川県" => "P37_postage",
			"愛媛県" => "P38_postage",
			"高知県" => "P39_postage",
			"福岡県" => "P40_postage",
			"佐賀県" => "P41_postage",
			"長崎県" => "P42_postage",
			"熊本県" => "P43_postage",
			"大分県" => "P44_postage",
			"宮崎県" => "P45_postage",
			"鹿児島県" => "P46_postage",
			"沖縄県" => "P47_postage"
		);

		return $add_pre_array;
	}

	//カートの件数を取得
	function getCartRow()
	{
		if(!$cart = Globals::session("CART_ITEM")) return 0;
		return count($cart);
	}

	function getCartDiscount()
	{
		global $sql;
        $no_discount = getDiscountItem();
		$next = 0;
		$discount = 0;
		$next_discount_par = 0;
		$cart_row_totale = 0;
		$cart_row_blank = 0;
		$discount_par = 0;
		$student = 0;
		if($cart = Globals::session("CART_ITEM"))
		{
			$item_price_totale = 0;
			$cart_price_totale = 0;
			foreach ($cart as $key => $val)
			{
			    if (is_mask($val['item_id'])) {
			        continue;
                }

				if(!isset($val["item_price"])){
					$val["item_price"]=0;
				}
				if(!isset($val["option_price"])){
					$val["option_price"]=0;
				}
				if(!isset($val["cart_price"])){
					$val["cart_price"]=0;
				}

                $isPrintByLayersActivated = isset($val["print_by_layers_activated"]) ? $val["print_by_layers_activated"] : '0';

                if ((!$isPrintByLayersActivated && !empty($val['product_type']) && $val['product_type'] != 'bl' ) || (Globals::session("LOGIN_TYPE") == "admin" && (empty($val['product_type']) || $val['product_type'] != 'bl' )) || (!empty($val['directory']) && $val['directory'] == "designenq")) {

                	if(!in_array($val['item_type'],$no_discount)){
						$item_price_totale += ($val["item_price"] + $val["option_price"]) * $val["cart_row"];
						if(isset($val["design_from"])) {
							if ($val["design_from"] == 'niko2') {
								$item_price_totale = ($item_price_totale * 0.8);
							}
						}
						$cart_price_totale += $val["cart_price"] * $val["cart_row"];
					}
                }

                if(!empty($val['product_type']) && $val['product_type'] == 'bl') {
                	$cart_row_blank += $val["cart_row"];
				}elseif(in_array($val['item_type'],$no_discount)){
					$cart_row_blank += $val["cart_row"];
				} else {
					$cart_row_totale += $val["cart_row"];
				}
			}

            list($next, $discount, $discount_par, $next_discount_par) = getDisCount($cart_row_totale, $item_price_totale);

			if(	Globals::session("CART_STUDENT")){
				$studenttable = "student_discount";

				$pay_total_tmp=$cart_price_totale-$discount;
				$tmp=$sql->selectRecord($studenttable, 1);
				$student=floor($pay_total_tmp*($tmp["rate"])/100);
			}
		}
		return array("cart_row_totale" => $cart_row_totale + $cart_row_blank, "next" => $next, "discount" => $discount, "discount_par" => $discount_par, "next_discount_par" => $next_discount_par,'student'=>$student);
	}

	//カートの中身をリフレッシュ
	function refreshCart()
	{
		global $sql;

        update_cart();
		// デザイン料計算
		$design_type_array = array();
        Globals::setSession('product_type', '');
		if($cart = Globals::session("CART_ITEM"))
		{
			foreach($cart as $key => $val)
			{
                if ($cart[$key]['product_type'] === 'bl' && empty($cart[$key]['item_type_size_detail'])) {
                    unset($cart[$key]);
                    continue;
                }

				if (empty(Globals::session('product_type')) && !empty($cart[$key]['product_type'])) {
                    Globals::setSession('product_type', $cart[$key]['product_type']);
				} elseif (!empty(Globals::session('product_type')) && !in_array(Globals::session('product_type'), [PRINT_LAZE, PRINT_EMBROIDERY]) && !empty($cart[$key]['product_type'])) {
                    if (Globals::session('product_type') != $cart[$key]['product_type']) {
                    	if($cart[$key]['product_type'] == 'bl' || $cart[$key]['product_type'] == TYPE_PRINT){
                    		Globals::setSession('product_type', TYPE_PRINT);
						} elseif($cart[$key]['product_type'] == LAZE_TYPE) {
                            Globals::setSession('product_type', PRINT_LAZE);
                    	} else {
							Globals::setSession('product_type', PRINT_EMBROIDERY);
						}
                    }
				}
				//calculate total of item
				$cart[$key]['cart_row'] = 0;
				if(isset($cart[$key]['item_type_size_detail'])) {
					foreach ($cart[$key]['item_type_size_detail'] as $itemTypeSize) {
						$cart[$key]['cart_row'] += $itemTypeSize['total'];
					}
				}

				// プリント料金
				$cart[$key]["front_print_price"] = 0;
				$cart[$key]["back_print_price"] = 0;
				$cart[$key]["left_print_price"] = 0;
				$cart[$key]["right_print_price"] = 0;
				if($its = $sql->selectRecord("master_item_type_sub", $val["item_type_sub"])) {
					if(empty($val['blank_item'])) {
						if (!empty($cart[$key]['embroidery_print'])) {
							$embroidery_print = json_decode($val['embroidery_print'], true);
							if (!empty($embroidery_print['embroidery'])) {

								$print_sides = [
									1 => 'front_print_price',
									2 => 'back_print_price',
									3 => 'left_print_price',
									4 => 'right_print_price',
								];

								for ($i = 1; $i <= 4; $i++) {
									$side = $i;
									if ($i == 4) {
										$side = 3;
									}

									if (!empty($embroidery_print['embroidery'][$i])) {
                                        $cart[$key][sprintf('embroidery_side_%s', $i)] = 0;
                                        getEmbroideryPrice($embroidery_print['embroidery'][$i], $cart[$key][sprintf('embroidery_side_%s', $i)], EMBROIDERY_PRICE[$i]);
									} else {
										$cart[$key][sprintf('embroidery_side_%s', $i)] = 0;
									}

									// print
									if (!empty($embroidery_print['print'][$i])) {
										$cart[$key][$print_sides[$i]] = $its[sprintf('cost%s', $side)];
									}
								}
							}
						} else {
							if ($val["image_path1"]) {
								$cart[$key]["front_print_price"] = $its["cost1"];
							}
							if ($val["image_path2"]) {
								$cart[$key]["back_print_price"] = $its["cost2"];
							}
							if ($val["image_path3"]) {
								$cart[$key]["left_print_price"] = $its["cost3"];
							}
							if ($val["image_path4"]) {
								$cart[$key]["right_print_price"] = $its["cost3"];
							}
						}
					}
				}

				// Fill values for printing by layers
                $cart[$key]['print_by_layers_activated'] = 0;
                $cart[$key]['is_layers_print_allowed'] = false;
                $cart[$key]['print_by_layers_plate_price'] = 0;
                $cart[$key]['print_by_layers_item_price'] = 0;
                $cart[$key]['print_by_layers_front_price'] = 0;
                $cart[$key]['print_by_layers_back_price'] = 0;
                $cart[$key]['print_by_layers_left_price'] = 0;
                $cart[$key]['print_by_layers_right_price'] = 0;
                if (!empty($val['print_by_layers_activated'])) {
                    $cart[$key]['print_by_layers_activated'] = $val['print_by_layers_activated'];
				}
				if (!empty($val['print_by_layers_data'])) {
                    $cart[$key]['is_layers_print_allowed'] = boolval($val['print_by_layers_data']['is_layers_print_allowed']);
                    if (!empty($val['print_by_layers_data']['price_details'])) {
                    	$priceDetails = $val['print_by_layers_data']['price_details'];
                        $cart[$key]['print_by_layers_plate_price'] = $priceDetails['base_price_for_production'];
                        $cart[$key]['print_by_layers_item_price'] = $priceDetails['product_price_for_single_item'];
                        if (!empty($priceDetails['sides']['front'])) {
                            $cart[$key]['print_by_layers_front_price'] = $priceDetails['sides']['front'];
						}
                        if (!empty($priceDetails['sides']['back'])) {
                            $cart[$key]['print_by_layers_back_price'] = $priceDetails['sides']['back'];
                        }
                        if (!empty($priceDetails['sides']['left_side'])) {
                            $cart[$key]['print_by_layers_left_price'] = $priceDetails['sides']['left_side'];
                        }
                        if (!empty($priceDetails['sides']['right_side'])) {
                            $cart[$key]['print_by_layers_right_price'] = $priceDetails['sides']['right_side'];
                        }
					}
				}

				//ID付与確認
				if(!isset($val["item_id"]) || !$val["item_id"]) continue;

				//DB有無、ステータス確認
				if(!$rec = $sql->selectRecord("item", $val["item_id"])) {
					unset($cart[$key]);
					continue;
				}
				else if(!$rec["state"]) {
					unset($cart[$key]);
					continue;
				}
				else {
					if(empty(Globals::session("orderBaseCart"))) {
                        //販売制限の最大値まで
                        if ($rec["buy_count_state"] && $rec["buy_count_row"] < $val["cart_row"]) $cart[$key]["cart_row"] = $rec["buy_count_row"];
                        if ($cart[$key]["cart_row"] <= 0) {
                            unset($cart[$key]);
                            continue;
                        }
                    }
                }

                $cart[$key]["cart_price"] = $cart[$key]["item_price"] + $cart[$key]["option_price"];
				//セルフ価格反映
				if(!(getSelfFlag($val["item_id"]) || $rec["user"] == Globals::session("LOGIN_ID"))){
                    $cart[$key]["cart_price"] += $rec["fee_user"];
				}

                if ($rec["owner"] !== Globals::session("LOGIN_ID")) {
                    $cart[$key]["cart_price"] += $rec["fee_owner"];
                }

				global $FREE_DESIGN_ITEM_ID;
				$tmp_rec3 = $sql->selectRecord("item", $val["item_id"]);
				// デザインお任せ商品
				if (($val["item_id"] == $FREE_DESIGN_ITEM_ID) || ($tmp_rec3["owner_item"] == $FREE_DESIGN_ITEM_ID))
				{
					if ($fdt = $cart[$key]["front_design_type"]) {
						$design_type_array[$fdt] = (isset($design_type_array[$fdt]) ? $design_type_array[$fdt] : 0) + $cart[$key]["cart_row"];

					}
					if ($bdt = $cart[$key]["back_design_type"]) {
						$design_type_array[$bdt] = (isset($design_type_array[$bdt]) ? $design_type_array[$bdt] : 0) + $cart[$key]["cart_row"];
					}
					if ($ldt = $cart[$key]["left_design_type"]) {
						$design_type_array[$ldt] = (isset($design_type_array[$ldt]) ? $design_type_array[$ldt] : 0) + $cart[$key]["cart_row"];
					}
					if ($rdt = $cart[$key]["right_design_type"]) {
						$design_type_array[$rdt] = (isset($design_type_array[$rdt]) ? $design_type_array[$rdt] : 0) + $cart[$key]["cart_row"];
					}

					// プリント料金
					if($its = $sql->selectRecord("master_item_type_sub", $val["item_type_sub"])) {
						if ($fdt = $cart[$key]["front_design_type"]) {
							$cart[$key]["cart_price"] = $cart[$key]["cart_price"] + $its["cost1"];
						}
						if ($bdt = $cart[$key]["back_design_type"]) {
							$cart[$key]["cart_price"] = $cart[$key]["cart_price"] + $its["cost2"];
						}
						if ($ldt = $cart[$key]["left_design_type"]) {
							$cart[$key]["cart_price"] = $cart[$key]["cart_price"] + $its["cost3"];
						}
						if ($rdt = $cart[$key]["right_design_type"]) {
							$cart[$key]["cart_price"] = $cart[$key]["cart_price"] + $its["cost3"];
						}
					}
				}

			}
			Globals::setSession("CART_ITEM", $cart);

			Globals::setSession("CART_DESIGN_NEXT", 3);
			$design_price = 0;
			$design_discount = 0;
			foreach($design_type_array as $key => $val)
			{
				if ($val < 3) { // 3枚未満だと1000円課金
					$design_price = $design_price + 1000;
				} else {
					$design_discount = $design_discount + 1000;
				}
				$tmpCnt = 3 - ($val % 3);
				if ($tmpCnt < Globals::session("CART_DESIGN_NEXT")) {
					Globals::setSession("CART_DESIGN_NEXT", $tmpCnt);
				}
			}
			Globals::setSession("CART_DESIGN_PRICE", $design_price);
			Globals::setSession("CART_DESIGN_DISCOUNT", $design_discount);
		}
	}

	//remove all cart is pasral or not pasral
	function removeCartPasral($cart_pasral = true){
		if($cart = Globals::session("CART_ITEM"))
		{
			foreach($cart as $key => $val){
				if($cart_pasral && !empty($val['product_type']) && ($val['product_type'] != RINGPASRAL)){
					unset($cart[$key]);
				}

				if(!$cart_pasral && !empty($val['product_type']) && ($val['product_type'] == RINGPASRAL)){
					unset($cart[$key]);
				}

			}
		}
		Globals::setSession("CART_ITEM", $cart);
	}

	function jsonEncode($data)
	{
		echo json_encode($data);
		exit;
	}

	function userSearchWord($id, $com = "update")
	{
		global $sql;

		$table = "user";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		$tmp = "";
		foreach($rec as $key => $val)
		{
			if($key == "search_word") continue;

			if($val)
			{
				if(is_array($val))
					$tmp .= implode("\t", $val)."\t";
				else
					$tmp .= $val."\t";
			}
		}

		if($com == "update")
		{
			$update = $sql->setData($table, null, "search_word", $tmp);
			$sql->updateRecord($table, $update, $rec["id"]);
		}
		return rtrim($tmp, "\t");
	}

	function itemSearchWord($rec)
	{
		global $sql;

		$tmp = "";
		foreach($rec as $key => $val)
		{
			if($key == "search_word") continue;

			if($val)
			{
				if(is_array($val))
					$tmp .= implode("\t", $val)."\t";
				else
					$tmp .= $val."\t";
			}
		}

		//デザイナー名更新
		if(isset($rec["user"]) && $rec["user"])
		{
			if($u_rec = $sql->selectRecord("user", $rec["user"]))
			{
				$tmp .= $u_rec["name"]."\t";
			}
		}
		else
		{
			if($i_rec = $sql->selectRecord("item", $rec["id"]))
			{
				if($u_rec = $sql->selectRecord("user", $i_rec["user"]))
				{
					$tmp .= $u_rec["name"]."\t";
				}
			}
		}

        if (!empty($rec["normal_cat_c1"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_c", $rec["normal_cat_c1"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_c2"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_c", $rec["normal_cat_c2"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_c3"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_c", $rec["normal_cat_c3"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_c4"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_c", $rec["normal_cat_c4"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_c5"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_c", $rec["normal_cat_c5"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_p1"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_p", $rec["normal_cat_p1"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_p2"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_p", $rec["normal_cat_p2"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_p3"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_p", $rec["normal_cat_p3"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_p4"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_p", $rec["normal_cat_p4"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["normal_cat_p5"])) {
            if ($u_rec = $sql->selectRecord("master_normal_cat_p", $rec["normal_cat_p5"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["category_1"])) {
            if ($u_rec = $sql->selectRecord("master_item_categories1", $rec["category_1"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["category_2"])) {
            if ($u_rec = $sql->selectRecord("master_item_categories2", $rec["category_2"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }
        if (!empty($rec["category_3"])) {
            if ($u_rec = $sql->selectRecord("master_item_categories3", $rec["category_3"])) {
                $tmp .= $u_rec["name"] . "\t";
            }
        }


		return mb_convert_kana(rtrim($tmp, "\t"), "KVr");
	}

	//ポイント履歴追加
	function updatePoint($user, $point, $pay_id = null, $log_time = '')
	{
		global $sql;
		if(!$u_rec = $sql->selectRecord("user", $user)) return false;
		if(!$u_rec["state"]) return false;

        $used_points = $available_points = 0;
        $table       = "upoints";
        $where       = $sql->setWhere($table, null, "state", "=", UPOINT_STATE['available']);
        $where       = $sql->setWhere($table, $where, "user", "=", $user);
        $order       = $sql->setOrder($table, null, "expiry", "ASC");
        $result      = $sql->getSelectResult($table, $where, $order);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $available_points += $rec['point'];

            if ($available_points >= $point) {
                if ($available_points == $point) {
                    $new = false;
                } else {
                    $new = true;
                }

                updateUpoint($rec["id"], UPOINT_STATE['used'], $point - $used_points, $log_time, false, $pay_id, $new);

                break;
            } else {
                updateUpoint($rec["id"], UPOINT_STATE['used'], 0, $log_time, false, $pay_id);
            }

            $used_points += $rec['point'];
        }

        //ポイント反映
        updatePointUser($user, -$point);
	}

	//ポイント更新
	function updatePointUser($user, $point = 0, $compare_point = 0)
	{
		global $sql;

		$table = "user";
		if(!$rec = $sql->selectRecord($table, $user)) return false;

        $user_point = 0;
        if ($point_rec = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT SUM(point) as point FROM upoints WHERE `user` = "%s" AND state = %s', $user, UPOINT_STATE['available'])))) {
            $user_point = $point_rec['point'];
        }

        if ($compare_point > 0 && $compare_point == $user_point) {
        	return $compare_point;
        }

        $update = $sql->setData($table, null, "point", $user_point);
        $sql->updateRecord($table, $update, $rec["id"]);

		//ポイント付与メール送信
		if($point > 0)
		{
			$rec["grant_point"] = $point;
			$rec["point"] = $update["point"];
			mail_templateFunc::sendMail("user", "grant_point", $rec["mail"], $rec);
		}

		return $user_point;
	}

	//対象ポイントのステータスを変更（ID指定）
	function changePointLog($pay_id, $user_id, $state, &$tmp_array = [])
	{
	    global $sql;

	    $upoints      = [];
	    $log_time     = '';
	    $user_point   = 0;
	    $update_point = false;
	    $table        = "point_log";
	    $tmp_where    = $sql->setWhere($table, null, "table_id", "=", $pay_id);
		$tmp_where    = $sql->setWhere($table, $tmp_where, "user", "=", $user_id);

		if ($state == PAY_STATE['cancel'])
			$tmp_where    = $sql->setWhere($table, $tmp_where, "state", 'IN', [UPOINT_STATE['used'], UPOINT_STATE['pending']]);
		else
			$tmp_where    = $sql->setWhere($table, $tmp_where, "state", 'IN', [UPOINT_STATE['pending']]);

        $tmp_where    = $sql->setWhere($table, $tmp_where, "table_id", 'NOT IN',
                                       sprintf('SELECT table_id FROM point_log WHERE `user` = "%s" AND table_id = "%s" AND state IN (%s, %s, %s)',
                                               $user_id, $pay_id, UPOINT_STATE['canceled'], UPOINT_STATE['returned'], UPOINT_STATE['expiry']), 'AND', '(');
        $tmp_where    = $sql->setWhere($table, $tmp_where, 'upoint_id', "=", '', 'OR', ')');
        $order        = $sql->setOrder($table, null, "regist_unix", "DESC");
        $result       = $sql->getSelectResult($table, $tmp_where, $order);

	    while ($pl_rec = $sql->sql_fetch_assoc($result)) {
            $point        = 0;
            $point_state  = $pl_rec['state'];

	        if ($state == PAY_STATE['cancel']) {
	            if ($pl_rec['state'] == UPOINT_STATE['used']) {
                    $update_point = true;
                    $point        = $pl_rec['point'];
                    $log_time     = $pl_rec['regist_unix'];
                    $point_state  = UPOINT_STATE['returned'];
                } else {
                    $point_state = UPOINT_STATE['canceled'];
                }
            } else {
	        	if (empty($upoints[$pl_rec['upoint_id']])) {
                    $upoint = $sql->selectRecord('upoints', $pl_rec['upoint_id']);

                    if (empty($upoint) || $upoint['state'] == UPOINT_STATE['used']) {
                        break;
                    }

                    $upoints[$pl_rec['upoint_id']] = $pl_rec['upoint_id'];
		        }

	            if ($pl_rec['state'] == UPOINT_STATE['pending']) {
	                $point_state  = UPOINT_STATE['available'];
	                $update_point = true;
	                $log_time     = $pl_rec['regist_unix'];

                    if (empty($pl_rec['upoint_id'])) {
                        $point_id = createUpoint($user_id, $pl_rec['point'], $pay_id, UPOINT_STATE['pending'], '', '', 0, '', false);

                        if (!empty($point_id)) {
                            $pl_rec['upoint_id'] = $point_id;
                            $update = $sql->setData($table, null, 'upoint_id', $point_id);
                            $sql->updateRecord($table, $update, $pl_rec["id"]);
                        }
                    }
	            }
	        }

            if ($point_state != $pl_rec['state']) {
                $tmp_array["pay_{$pay_id}"]["point_log_" . $pl_rec["id"]] = ["state_old" => $pl_rec['state']];
                $u_point = updateUpoint($pl_rec['upoint_id'], $point_state, $point, $log_time, true, $pay_id);

                if ($update_point) {
                    $user_point += $u_point;
                }
            }
	    }

	    if ($update_point) {
	        updatePointUser($user_id, $user_point);
	    }
    }

	function obj2arr($obj)
	{
		if(!is_object($obj) && !is_array($obj))
		{
			return $obj;
		}

		if(is_object($obj))
		{
			$array = (array)$obj;
		}
		else
		{
			$array = $obj;
		}

		foreach($array as &$val)
		{
			$val = obj2arr($val);
		}
		return $array;
	}

	function checkPayTypeAfter($id, $state)
	{
		global $sql;

		if(!$p_rec = $sql->selectRecord("pay", $id)) return 2;
		if($p_rec["pay_type"] != "after") return 1;

		if(!$al_rec = $sql->keySelectRecord("after_log", "pay_id", $id)) return 3;

		if($state == 1)
		{
			if($al_rec["state"] != 11) return 4;
		}
		if($state == 2)
		{
			if($al_rec["state"] != 4) return 3;
		}

		if($al_rec["slipno"] == "") return 5;
		if($al_rec["pdcompanycode"] == 0) return 5;
		if($al_rec["gmo_transaction_id"] == "") return 6;

		return 1;
	}

	function getImageList($id)
	{
		global $sql;

		$tmp_table = "pay_item";
		$tmp_where = $sql->setWhere($tmp_table, null, "pay", "=", $id);
		$tmp_order = $sql->setOrder($tmp_table, null, "id", "ASC");
		$tmp_result = $sql->getSelectResult($tmp_table, $tmp_where, $tmp_order);

		$img_list = array();
		while($tmp_rec = $sql->sql_fetch_assoc($tmp_result))
		{
			if($tmp_rec["item"])
			{
				if($tmp2_rec = $sql->selectRecord("item", $tmp_rec["item"]))
				{
					$ids =  "id=". $id . " / pay_item_id=". $tmp_rec["id"] ." / item_id=". $tmp2_rec["id"];

					$img = array();
					$img[0] = $ids;
					$img[1] = $tmp2_rec["item_preview1"];
					$img[2] = $tmp2_rec["item_preview2"];
					$img[3] = $tmp2_rec["item_preview3"];
					$img[4] = $tmp2_rec["item_preview4"];
					$img[5] = $tmp2_rec["item_image1"];
					$img[6] = $tmp2_rec["item_image2"];
					$img[7] = $tmp2_rec["item_image3"];
					$img[8] = $tmp2_rec["item_image4"];

					$img_list[$tmp_rec["item_name"] ."_". (count($img_list) + 1)] = $img;
				}
			}
		}

		return $img_list;
	}

	function getCartGiftTotale()
	{
		if(!$gift = Globals::session("CART_GIFT")) return 0;

		$s_rec = SystemUtil::getSystemParam();
		$gift_array = array(
			"pink",
			"blue",
			"yellow",
			);
		$total_cost = 0;
		foreach ($gift_array as $key => $val)
		{
			$total_cost += $s_rec["gift_set_cost"] * $gift[$val]["gift_row"];
		}
		return $total_cost;
	}

	function getCartGiftRow($color = "all")
	{
		if(!$gift = Globals::session("CART_GIFT")) return 0;

		$gift_array = array(
			"pink",
			"blue",
			"yellow",
			);

		$total_row = 0;
		switch ($color)
		{
			case 'pink':
			case 'blue':
			case 'yellow':
				$total_row = $gift[$color]["gift_row"];
				break;
			case 'all':
			default :
				foreach ($gift_array as $key => $val)
				{
					$total_row += $gift[$val]["gift_row"];
				}
				break;
		}
		return $total_row;
	}

	function sendDesignFreeMail($rec, $item) {
		require_once __DIR__ . '/../designenq/jphpmailer.php';

		global $sql;
		ini_set('memory_limit', '40M');
		$data = array();
        $sizes = [];
        $delivery_note = '';
        $has_delivery_note = 'なし';

        if (isset($rec['send_delivery_slip']) && $rec['send_delivery_slip'] == 1) {
            $has_delivery_note = 'あり';
        }

        if (!empty($rec['remarks'])) {
            $delivery_note = $rec['remarks'];
        }

		$isTransparentBackground = false;
		if($cart = Globals::session("CART_ITEM"))
		{
			foreach($cart as $key => $val)
			{
				if (isset($val["direct_mode"]) && ($val["direct_mode"] == "design_free" || $val["direct_mode"] == "transparent_background")) {
					if ($val["direct_mode"] == "transparent_background") {
						$isTransparentBackground = true;
					}

					$item_sizes = [];

					foreach ($val['item_type_size_detail'] as $size_id => $item_size) {
                        if (!empty($sizes[$key])) {
                            $a_size = $sizes[$key];
						} else {
                        	$a_size = $sql->selectRecord('master_item_type_size', $size_id);
                        	$sizes[$size_id] = $a_size;
						}

                        $item_sizes[] = ['name' => $a_size['name'], 'quantity' => $item_size['total']];
					}
					array_push($data, array(
							'item_name' => $val["item_name"],
							'color' => $val["color"],
							'design' => $val["design"],
							'sizes' => $item_sizes
					));
				}
			}
		}

		if (count($data) == 0) {
			return;
		}

		$p_rec = $sql->selectRecord("pay", $rec["id"]);

		header("Content-Type: text/html;charset=UTF-8");
		header('Cache-Control: no-store');
		$sendmail="info@up-t.jp";
		$admin_mail="design@up-t.jp";
		//$admin_mail="ikuta@full-link.jp";
/*
		$subject ="デザインのご相談を承りました";
		$msg='';
		$msg=$msg."".$_SESSION["form"]['name']."様\n";
		$msg=$msg."この度は、デザインのご相談をいただきありがとうございます。
以下はお客様がメールフォームから送信された内容です。
ご確認ください。\n";
		$msg=$msg."==========================================\n";
		$msg=$msg."■お名前\n".$_SESSION["form"]['name']."\n";
		$msg=$msg."■お電話番号\n".$_SESSION["form"]['telno']."\n";
		$msg=$msg."■郵便番号\n".$_SESSION["form"]['add_num']."\n";
		$msg=$msg."■都道府県\n".$_SESSION["form"]['add_pre']."\n";
		$msg=$msg."■住所(市町村番地)\n".$_SESSION["form"]['add_sub']."\n";
		$msg=$msg."■住所(建物名)\n".$_SESSION["form"]['add_sub2']."\n";
		$msg=$msg."■メールアドレス\n".$_SESSION["form"]['mailaddr']."\n";

		foreach ($data as $k => $d) {
			$v = $data[$k];
			$msg=$msg."----------------------------\n";
			$msg=$msg."■アイテム\n".$v['item_name']."\n";
			$msg=$msg."■カラー\n".$v['color']."\n";
			$msg=$msg."■デザイン箇所\n".$v['design']."\n";
			$msg=$msg."■サイズ\n".$v['size']."\n";
			$msg=$msg."■枚数\n".$v['quantity']."\n";
			$msg=$msg."■デザインに関してのご要望\n".$v['contents']."\n";
			$msg=$msg."■参考デザイン\n".$v['file_name']."\n";
			$msg=$msg."----------------------------\n";
		}

		$msg=$msg."==========================================



ご相談内容を確認後、１週間以内にご返信させていただきます。
１週間経っても連絡がない場合は、お手数ですが下記連絡先までお問い合わせ下さい。

どうぞ、よろしくお願いいたします。

☆★☆★☆★☆★☆★☆★☆★☆★☆★☆★
みんなが違うから世界は楽しい
Up-T（アップティー）カスタマーサポート
http://up-t.jp

facebookページ
https://www.facebook.com/upt11/

お問合せ：info@up-t.jp

☆★☆★☆★☆★☆★☆★☆★☆★☆★☆★
コミュニケーションをクリエイトする
LINEスタンプ制作代行「スタンプクリエイション」
http://stamp-creation.com/
☆★☆★☆★☆★☆★☆★☆★☆★☆★☆★

	";
		$mail = new JPHPMailer();

		$mail->addTo($_SESSION["form"]['mailaddr']);
		$mail->setFrom($p_rec['mail'],$p_rec['mail']);
		$mail->setSubject($subject);

		$mail->setBody($msg);
		if (!$mail->send()){
			die("メール送信失敗=>".$mail->getErrorMessage());
		}
*/

		// 管理者向け
		$mail = new JPHPMailer();

		$subject ="デザインの注文がありました";
		$msg='';
		$msg=$msg."==========================================\n";
		$msg=$msg."■お名前\n".$p_rec['name']."\n";
		$msg=$msg."■お電話番号\n".$p_rec['tel']."\n";
		$msg=$msg."■郵便番号\n".$p_rec['add_num']."\n";
		$msg=$msg."■都道府県\n".$p_rec['add_pre']."\n";
		$msg=$msg."■住所(市町村番地)\n".$p_rec['add_sub']."\n";
		$msg=$msg."■住所(建物名)\n".$p_rec['add_sub2']."\n";
		$msg=$msg."■メールアドレス\n".$p_rec['mail']."\n";
		$msg=$msg."■注文番号\n".$p_rec['pay_num']."\n";
        $msg=$msg."■納品書\n". $has_delivery_note ."\n";

        if (!empty($delivery_note)) {
            $msg=$msg."■メモ\n". $delivery_note ."\n";
        }

		if ($isTransparentBackground) {
			$msg=$msg."■デザイン種別\n"."背景透過"."\n";
		} else {
			$msg=$msg."■デザイン種別\n"."デザイン制作"."\n";
		}

		foreach ($data as $k => $d) {
			$v = $data[$k];
			$msg=$msg."----------------------------\n";
			$msg=$msg."■アイテム\n".$v['item_name']."\n";
			$msg=$msg."■カラー\n".$v['color']."\n";
			$msg=$msg."■デザイン箇所\n".$v['design']."\n";

			foreach ($v['sizes'] as $size) {
                $msg=$msg."■サイズ\n". $size['name'] ."\n";
                $msg=$msg."■枚数\n". $size['quantity'] ."\n";
			}

			//$msg=$msg."■デザインに関してのご要望\n".$v['contents']."\n";
			//$msg=$msg."■参考デザイン\n".$v['file_name']."\n";
			$msg=$msg."----------------------------\n";

			//if($v['file_path']){
			//	$mail->addAttachment($v['file_path']);
			//}
		}

        if ($item = Globals::session("DESIGN_IMAGES")) {
            foreach ($item as $key => $val) {
                $msg = $msg . "■デザインに関してのご要望\n" . $val['contents'] . "\n";
                $msg = $msg . "■参考デザイン\n";

                foreach ($val['image_urls'] as $url) {
                    $msg = $msg . $url . "\n";
                }
            }
        }

		if($item = Globals::session("DESIGN_ITEM"))
		{
			foreach($item as $key => $val)
			{
				$msg=$msg."■デザインに関してのご要望\n".$val['contents']."\n";
				$msg=$msg."■参考デザイン\n";

				foreach($val['file_data'] as $f)
				{
					$msg=$msg.$f[0]."\n";
					$mail->addAttachment($f[1]);
				}
			}
		}

		$msg=$msg."==========================================";

		$mail->addTo($admin_mail);
		$mail->setFrom($p_rec['mail'],$p_rec['mail']);
		$mail->setSubject($subject);

		$mail->setBody($msg);
		if (!$mail->send()){
			die("メール送信失敗=>".$mail->getErrorMessage());
		}
	}

	function chargeAmazon($amount, $checkout_session_id, $userId){

		global $amazonpay_config;
		$errorText = 'Amazon Payでの決済に失敗しました。他のお支払い方法を選択してください';

		$client = new AmazonPayV2\Client($amazonpay_config);
		$result = $client->getCheckoutSession($checkout_session_id);

		$json_result = json_decode($result['response']);
		$state = $json_result->statusDetail->state;

		if('Completed' != $state){
			throw new \Exception($errorText);
		}

	// --------------------------

		$headers = array('x-amz-pay-Idempotency-Key' => Globals::session('idempotency_key'));

		$payload = array(
			'captureAmount' => array(
				'amount' => $amount,
				'currencyCode' => "JPY"
			)
		);

		$result = $client->captureCharge($json_result->chargeId, $payload, $headers);

		if($result['status'] != 200){
			throw new \Exception($errorText);
		}

		$data = array(
			'closureReason' => 'No more charges required',
			'cancelPendingCharges' => false
		);

		$result_close_order = $client->closeChargePermission($json_result->chargePermissionId, $data);

		if($result_close_order['status'] != 200){
			throw new \Exception($errorText);
		}

	// ----------------------------

		global $sql;
		//基本情報を登録
		$table = "amazon_pay_log";
		$id = SystemUtil::getUniqId($table, false, true);

		$tmp_rec = $sql->setData($table, null, "id", $id);
		$tmp_rec = $sql->setData($table, $tmp_rec, "user_id", $userId);
		$tmp_rec = $sql->setData($table, $tmp_rec, "state", 1);
		$tmp_rec = $sql->setData($table, $tmp_rec, "regist_unix", time());
		$sql->addRecord($table, $tmp_rec);

		Globals::setSession('amazon_capture_id', $json_result->chargeId);

	}

	function refundAmazon($amount, $amazonCaptureId) {
		global $amazonpay_config;

		$errorText = 'Refund error';

		$payload = array(
			'chargeId' => $amazonCaptureId,
			'refundAmount' => array(
				'amount' => $amount,
				'currencyCode' => "JPY"
			)
		);
		$headers = array('x-amz-pay-Idempotency-Key' => uniqid());
		$client = new AmazonPayV2\Client($amazonpay_config);
		$result = $client->createRefund($payload, $headers);

		$json_result = json_decode($result['response']);

		if($result['status'] != 201){
			throw new \Exception($errorText);
		}
	}

	function randomString() {
		$chars = 'abcdefghijklmnopqrstuvwxyz';
		$str = '';
		for ($i = 0; $i < 10; $i++) {
			$str .= $chars[rand(0, strlen($chars)-1)];
		}
		return date('Ymd-') . $str;
	}

	function registCompMerge2order($table, $rec)
	{
		global $sql;

		$s_rec = SystemUtil::getSystemParam();

		//メール送信
		switch($rec["pay_type"])
		{
			case 'cod':
			case 'after2':
				mail_templateFunc::sendMail("admin", $table."_".$rec["pay_type"], $s_rec["mail"], $rec);

				if($user_rec = $sql->selectRecord("user", $rec["user"]))
					mail_templateFunc::sendMail("user", $table."_".$rec["pay_type"], $user_rec["mail"], $rec);

				break;
		}
		if($rec["pay_type"] == "after")
		{
			$al_table = "after_log";

			$array_tmp = array(
				0 => "HTTP_ACCEPT",
				1 => "HTTP_ACCEPT_CHARSET",
				2 => "HTTP_ACCEPT_ENCODING",
				3 => "HTTP_ACCEPT_LANGUAGE",
				4 => "",
				5 => "HTTP_CONNECTION",
				6 => "",
				7 => "HTTP_HOST",
				8 => "HTTP_REFERER",
				9 => "HTTP_USER_AGENT",
				10 => "",
				11 => "",
				12 => "",
				13 => ""
			);

			$other_array = array(
				"CONTENT_LENGTH"
			);

			$header_data = "";

			foreach ($array_tmp as $key => $val)
			{
				if($val == "")
				{
					$header_data .= ";:";
					continue;
				}

				if(array_key_exists($val, $_SERVER))
				{
					if($val == "HTTP_HOST" && $_SERVER[$val] == "")
					{
						$header_data .= "null;:";
					}
					else
					{
						$header_data .= $_SERVER[$val].";:";
					}
				}
				else
				{
					if($val == "HTTP_HOST")
					{
						$header_data .= "null;:";
					}
					else
					{
						$header_data .= ";:";
					}
				}
			}

			$header_data_tmp = "";
			foreach ($other_array as $key => $val)
			{
				if(array_key_exists($val, $_SERVER))
				{
					$header_data_tmp .= $val."--".$_SERVER[$val]."::";
				}
			}
			$header_data .= preg_replace('/::$/', "", $header_data_tmp).";:";

			$header_data .= $_SERVER["REMOTE_ADDR"].";:";
			$header_data .= "null;:";

			$al_rec = $sql->setData($al_table, null, "id", SystemUtil::getUniqId($al_table, false, true));
			$al_rec = $sql->setData($al_table, $al_rec, "user_type", "user");
			$al_rec = $sql->setData($al_table, $al_rec, "user_id", $rec["user"]);
			$al_rec = $sql->setData($al_table, $al_rec, "pay_type", $rec["pay_type"]);
			$al_rec = $sql->setData($al_table, $al_rec, "pay_id", $rec["id"]);
			$al_rec = $sql->setData($al_table, $al_rec, "http_header", $header_data);
			$al_rec = $sql->setData($al_table, $al_rec, "device_info", "");
			$al_rec = $sql->setData($al_table, $al_rec, "state", 0);
			$al_rec = $sql->setData($al_table, $al_rec, "regist_unix", $rec["regist_unix"]);
			$sql->addRecord($al_table, $al_rec);
		}
		if($rec["pay_type"] == "after2")
		{
			$al_table = "after_log2";

			$array_tmp = array(
				0 => "HTTP_ACCEPT",
				1 => "HTTP_ACCEPT_CHARSET",
				2 => "HTTP_ACCEPT_ENCODING",
				3 => "HTTP_ACCEPT_LANGUAGE",
				4 => "",
				5 => "HTTP_CONNECTION",
				6 => "",
				7 => "HTTP_HOST",
				8 => "HTTP_REFERER",
				9 => "HTTP_USER_AGENT",
				10 => "",
				11 => "",
				12 => "",
				13 => ""
			);

			$other_array = array(
				"CONTENT_LENGTH"
			);

			$header_data = "";

			foreach ($array_tmp as $key => $val)
			{
				if($val == "")
				{
					$header_data .= ";:";
					continue;
				}

				if(array_key_exists($val, $_SERVER))
				{
					if($val == "HTTP_HOST" && $_SERVER[$val] == "")
					{
						$header_data .= "null;:";
					}
					else
					{
						$header_data .= $_SERVER[$val].";:";
					}
				}
				else
				{
					if($val == "HTTP_HOST")
					{
						$header_data .= "null;:";
					}
					else
					{
						$header_data .= ";:";
					}
				}
			}

			$header_data_tmp = "";
			foreach ($other_array as $key => $val)
			{
				if(array_key_exists($val, $_SERVER))
				{
					$header_data_tmp .= $val."--".$_SERVER[$val]."::";
				}
			}
			$header_data .= preg_replace('/::$/', "", $header_data_tmp).";:";

			$header_data .= $_SERVER["REMOTE_ADDR"].";:";
			$header_data .= "null;:";

			$al_rec = $sql->setData($al_table, null, "id", SystemUtil::getUniqId($al_table, false, true));
			$al_rec = $sql->setData($al_table, $al_rec, "user_type", "user");
			$al_rec = $sql->setData($al_table, $al_rec, "user_id", $rec["user"]);
			$al_rec = $sql->setData($al_table, $al_rec, "pay_type", $rec["pay_type"]);
			$al_rec = $sql->setData($al_table, $al_rec, "pay_id", $rec["id"]);
			$al_rec = $sql->setData($al_table, $al_rec, "http_header", $header_data);
            $al_rec = $sql->setData($al_table, $al_rec, "device_info", "");
            $al_rec = $sql->setData($al_table, $al_rec, "state", 0);
            $al_rec = $sql->setData($al_table, $al_rec, "regist_unix", $rec["regist_unix"]);
            $sql->addRecord($al_table, $al_rec);
		}
		if($rec["pay_type"] == "cod" || $rec["pay_type"] == "card")
		{
			changePayPay($rec["id"], 1);
		}
	}

	function mergePayItem($order1, $order2, $newPayId){
		global $sql;
		$table = 'pay_item';
		$payItemOrder1 = $sql->queryRaw("pay_item", "Select * from pay_item where pay = '$order1'");
		$payItemOrder2 = $sql->queryRaw("pay_item", "Select * from pay_item where pay = '$order2'");

		if($payItemOrder1 && $payItemOrder2) {
			foreach ($payItemOrder1 as $key => $val) {
				$val['pay'] = $newPayId;
				$val['id'] = SystemUtil::getUniqId($table, false, true);
				$sql->addRecord($table, $val);
			}
			foreach ($payItemOrder2 as $key => $val) {
				$val['pay'] = $newPayId;
				$val['id'] = SystemUtil::getUniqId($table, false, true);
				$sql->addRecord($table, $val);
			}
		}
	}

	function getStoreInfo($userId,$type = null) {
		global $sql;
		$table = 'store_info';
        $name = 'user_id';
        if (!is_null($type)) {
            $table = $type;
            $name = 'user';
        }
		$where = $sql->setWhere($table, '', $name, '=', $userId);
		$listStore = $sql->getSelectResult($table, $where);

		return $listStore;
	}

	function addStore($data) {
		global $sql;
		$table = 'store_info';

		if($user = Globals::session('LOGIN_ID')) {
			$isExistStore = $sql->selectRecord('store_info', $data['store_id']);
			if(!$isExistStore) {
				$store['id'] = SystemUtil::getUniqId('store_info', false, true);
				$store['user_id'] = $user;
				$store['shop_name'] = $data['store_shop_name'];
				$store['represent_name'] = $data['store_represent_name'];
				$store['url'] = $data['store_url'];
				$store['phone_number'] = $data['store_phone_number'];
				$store['add_num'] = $data['store_add_num'];
				$store['add_pre'] = $data['store_add_pre'];
				$store['add_sub'] = $data['store_add_sub'];
				$store['add_sub2'] = $data['store_add_sub2'];
				$store['contact'] = $data['store_contact'];
				$store['message'] = $data['store_message'];

				$sql->addRecord($table, $store);
			}
		}
	}

	function changeMasterIsMain($table, $id)
	{
		global $sql;

        if(!$rec = $sql->selectRecord($table, $id)) return;

		if($table == "master_item_type_sub" || $table == "master_item_type_size"){
            $where = $sql->setWhere($table, null, 'item_type', '=', $rec["item_type"]);
            $where = $sql->setWhere($table, $where, 'is_main', '=', 1);

            $update = $sql->setData($table, null, "is_main", 0);
            $sql->updateRecordWhere($table, $update, $where);

            $update = $sql->setData($table, null, "is_main", 1);
            $sql->updateRecord($table, $update, $rec["id"]);
		}
		elseif ($table == "master_item_type"){
            $where = $sql->setWhere($table, null, 'is_main', '=', 1);
            $update = $sql->setData($table, null, "is_main", 0);
            $sql->updateRecordWhere($table, $update, $where);

            $update = $sql->setData($table, null, "is_main", 1);
            $sql->updateRecord($table, $update, $rec["id"]);

            $table = "master_categories";
            $where = $sql->setWhere($table, null, 'is_main', '=', 1);
            $update = $sql->setData($table, null, "is_main", 0);
            $sql->updateRecordWhere($table, $update, $where);

            $update = $sql->setData($table, null, "is_main", 1);
            $sql->updateRecord($table, $update, $rec["category_id"]);
		}
  	}

	/**
	 * Get up point
	 *
	 * @return float|int|mixed|null
	 */
	function getUpPoint()
	{
	    $item_total          = getCartPrice();
	    $gift_total          = getCartGiftTotale();
	    $cart_discount_array = getCartDiscount();
	    $discount_rank = Extension::discountPrice([2 => 'discount_rank']);
	    $discount_promotion_code = 0;
        if (!empty(Globals::session('discount_promotion_code'))) {
            $discount_promotion_code = Globals::session('discount_promotion_code');
        }

	    return $item_total - $cart_discount_array["discount"] - $discount_rank + $gift_total - $discount_promotion_code;
	}

	/**
	 * Get pay fee of post pay
	 *
	 * @param $pay_type
	 * @return int
	 */
	function getFeePostPay($pay_type)
	{
	    if ($pay_type == 'after2') {
            global $sql;

            $deferred_payment = $sql->selectRecord("deferred_payment", "1");

            return $deferred_payment["price"];
	    }

	    return 0;
	}

	function showNoteMessage($id)
	{
        global $sql;
        $message = '';

        if ($id){
            $note = $sql->selectRecord('master_item_note', $id);
            if ($note) {
                $message = $note['message'];
            }
		}
		return $message;
	}

	function showNoteTitle($id)
	{
    	global $sql;
    	$title = '';

    	if ($id){
       	 $note = $sql->selectRecord('master_item_note', $id);
        	if ($note) {
            	$title = $note['note_title'];
        	}
    	}
    	return $title;
	}

	function showListNote()
	{
		global $sql;

		$query = "SELECT * FROM master_item_note";

        $result = $sql->rawQuery($query);

        $list_note = "";

		while($note = $sql->sql_fetch_assoc($result)) {
            $list_note[] = $note;
		}

        return $list_note;
	}

	function getListBlankItem($itemId, $color = null, $size = null) {
		global $sql;

		$item = $sql->selectRecord("master_item_type", $itemId);

		$query = sprintf("SELECT
						blank_item_stock.id,
						blank_item_stock.item_sub_code,
						blank_item_stock.item_size_code,
						blank_item_stock.stock,
						blank_item_stock.expected_import_date,
						master_item_type_sub.id AS item_type_sub_id,
						master_item_type_sub.name AS item_type_sub_name,
						master_item_type_size.id AS item_type_size_id,
						master_item_type_size.name AS item_type_size_name
 						FROM blank_item_stock
						INNER JOIN master_item_type_sub ON blank_item_stock.item_sub_code = master_item_type_sub.item_code AND master_item_type_sub.state = 1 AND master_item_type_sub.item_type = '%s'
						INNER JOIN master_item_type_size ON blank_item_stock.item_size_code = master_item_type_size.item_code AND master_item_type_size.state = 1 AND master_item_type_size.item_type = '%s'
						WHERE blank_item_stock.item_code = '%s' ORDER BY master_item_type_size.wait", $itemId, $itemId, $item['item_code']);

		/*get item by color or size*/
		if (!empty($color) && empty($size)) {

			$query .= sprintf(' AND blank_item_stock.item_sub_code = \'%s\'',$color) ;
		} elseif (empty($color) && !empty($size)) {

			$query .= sprintf(' AND master_item_type_size.name = \'%s\'',$size) ;
		}

		$listItem = [];
        $blank_item_stocks = $sql->rawQuery($query);
		$listStockItem = array();
		while ($rec = $sql->sql_fetch_assoc($blank_item_stocks)) {
			$listItem[$rec['item_type_sub_id']][$rec['item_type_size_name']] = $rec;
			$data['list_size'][$rec['item_type_size_name']] =  $rec['item_type_size_name'];

			$listStockItem[$rec['item_type_sub_id']][$rec['item_type_size_id']]['stock'] = $rec['stock'];
			$listStockItem[$rec['item_type_sub_id']][$rec['item_type_size_id']]['item_type_size_name'] = $rec['item_type_size_name'];
			$listStockItem[$rec['item_type_sub_id']][$rec['item_type_size_id']]['item_type_sub_name'] = $rec['item_type_sub_name'];
		}
		Globals::setItems($listStockItem,"LIST_STOCK_ITEM");
		$data['item_code_nominal'] = $item['item_code_nominal'];
		$data['item_name'] = $item['name'];
		$data['list_item'] = $listItem;

		return $data;
	}

	function countListBlankItem($itemId, $itemCode = null) {
		global $sql;

		if (empty($itemCode)) {
			$item = $sql->selectRecord("master_item_type", $itemId);
			$itemCode = $item['item_code'];
		}

		$query = sprintf("SELECT
						blank_item_stock.id,
						blank_item_stock.item_sub_code,
						blank_item_stock.item_size_code,
						blank_item_stock.stock,
						blank_item_stock.expected_import_date,
						master_item_type_sub.id AS item_type_sub_id,
						master_item_type_sub.name AS item_type_sub_name,
						master_item_type_size.id AS item_type_size_id,
						master_item_type_size.name AS item_type_size_name
 						FROM blank_item_stock
						INNER JOIN master_item_type_sub ON blank_item_stock.item_sub_code = master_item_type_sub.item_code AND master_item_type_sub.item_type = '%s'
						INNER JOIN master_item_type_size ON blank_item_stock.item_size_code = master_item_type_size.item_code AND master_item_type_size.item_type = '%s'
						WHERE blank_item_stock.item_code = '%s'", $itemId, $itemId, $itemCode);

		$result = $sql->queryRaw('blank_item_stock', $query);

		return $result->num_rows;
	}

	function countBlankItem($item_type)
	{
		global $sql;
        $stock = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT stock FROM blank_item_stock_numbers WHERE item_type = "%s"', $item_type)));

        if (!empty($stock)) {
        	return (int)$stock['stock'];
		}

		return 0;
	}

	// get_rank_user
	function get_rank_user($userid)
	{
		global $sql;
		$query_get_rank  = "SELECT * FROM user WHERE id = '".$userid."'";
		$rec_user = $sql->sql_fetch_assoc($sql->rawQuery($query_get_rank));
        $query_get_discount  = "SELECT * FROM system";
        $rec_rank = $sql->sql_fetch_assoc($sql->rawQuery($query_get_discount));
        if($rec_rank){
            if($rec_user["rank"]== 'gold') {
                return $rec_rank["sale_gold_rank"];
            }
            if($rec_user["rank"]== 'silver') {
                return $rec_rank["sale_silver_rank"];
            }
        }
			return 0;
	}
	function get_system_constan()
	{
        global $sql;
        $query_get_system  = "SELECT * FROM system";
        $rec_system = $sql->sql_fetch_assoc($sql->rawQuery($query_get_system));

        return $rec_system;
	}
function checkPhoneCase($item_type, $flag = true)
{
    $it = [];
    $check = 0;

    if (empty(Globals::getItems("ITEM_PHONE_CASE"))) {
        global $sql;
        $clume = $sql->setClume('master_item_type', null, 'id');
        $where = $sql->setWhere('master_categories', null, 'master_category_style_id', '=', 4);
        $inner_join = $sql->setInnerJoin('master_item_type', 'master_categories', 'id', 'master_item_type', 'category_id');
        $result = $sql->getSelectResult('master_categories', $where, null, null, $clume, null, $inner_join);

        while ($rec = $sql->sql_fetch_assoc($result)) {
            $it[] = $rec['id'];
        }

        Globals::setItems($it, "ITEM_PHONE_CASE");
    } else {
        $it = Globals::getItems("ITEM_PHONE_CASE");
    }

    if ($flag == false) {
        return $it;
    }

    if (in_array($item_type, $it)) {
        $check = 1;
    }
    return $check;
}

	function get_number_pay_user($userId)
	{
        global $sql;

        $rec_system = get_system_constan();

        $yearCurent = date('Y');
        $monthCurent = date('m');
        $start_month = $monthCurent - $rec_system["rank_month"] + 1;
        $yearPre = $yearCurent -1;
        if($monthCurent < $rec_system["rank_month"]){
            $start_month = (12 + $monthCurent)-$rec_system["rank_month"] + 1;
            $conditionMonth = "(( date_m > ".$start_month." AND date_y = ".$yearPre." )
                                    OR
                                    ( date_m <= ".$monthCurent." AND date_y = ".$yearCurent." ))
                                    ";
        }
        else{
            $conditionMonth = "date_m > ".$start_month."
                                    AND date_m <= ".$monthCurent."
                                    AND date_y =  ".$yearCurent."
                                    ";
        }

        $query_get_pay_by_user = "SELECT count(id) as numberpay FROM pay WHERE user = '".$userId."' AND delivery_state = 1 AND regist_unix >= 1554044400 AND ".$conditionMonth." AND useragent LIKE 'Mozilla%' " ;
        $numberpay = $sql->sql_fetch_assoc($sql->rawQuery($query_get_pay_by_user));

        return $numberpay;

	}

	function get_rank_by_id($userId, $topPage = false)
	{
		global $sql;
        $rec_system = get_system_constan();
        $numberpay =  get_number_pay_user($userId);

		$query_get_rank  = "SELECT * FROM user WHERE id = '".$userId."'";
		$rec_user = $sql->sql_fetch_assoc($sql->rawQuery($query_get_rank));

		if(!empty($rec_user)){
			if($rec_user["rank"] == 'gold') {
                if ($topPage) {
                    $stringrt ="現在ゴールドランク（".$rec_system["sale_gold_rank"]."%オフ）です。";
				} else {
					$stringrt ="現在ゴールドランク（".$rec_system["sale_gold_rank"]."%オフ）です。";
				}
			} elseif($rec_user["rank"] == 'silver') {
				if ($topPage) {
					if($rec_system["times_of_gold_rank"] <= $numberpay["numberpay"]){
                        $stringrt ="現在シルバーランク（".$rec_system["sale_silver_rank"]."%オフ）です。<br/>おめでとうございます。次回よりゴールドランク(常時".$rec_system["sale_gold_rank"]."%オフ）にランクアップします。";
					} else {
						$stringrt = "さらに" . ($rec_system["times_of_gold_rank"] - $numberpay["numberpay"]) . '回購入で' . $rec_system["sale_gold_rank"] . '%オフになります';
					}
				} else {
					if($rec_system["times_of_gold_rank"] <= $numberpay["numberpay"]){
						$stringrt ="現在シルバーランク（".$rec_system["sale_silver_rank"]."%オフ）です。<br/>おめでとうございます。次回よりゴールドランク(常時".$rec_system["sale_gold_rank"]."%オフ）にランクアップします。";
					} else {
						$stringrt ="現在シルバーランク（".$rec_system["sale_silver_rank"]."%オフ）です。<br/>今月中に残り".($rec_system["times_of_gold_rank"] - $numberpay["numberpay"])."回のご購入で来月からゴールドランク（".$rec_system["sale_gold_rank"]."%オフ）になります。";
					}
				}
			} else {
				if ($topPage) {
					if($rec_system["times_of_gold_rank"] <= $numberpay["numberpay"]){
                        $stringrt ="現在ブロンズランクです。<br/>おめでとうございます。次回よりゴールドランク(常時".$rec_system["sale_gold_rank"]."%オフ）にランクアップします。";
					}
					elseif($rec_system["times_of_silver_rank"] <= $numberpay["numberpay"]){
						$stringrt = "さらに" . ($rec_system["times_of_gold_rank"] - $numberpay["numberpay"]) . '回購入で' . $rec_system["sale_gold_rank"] . '%オフになります';
					}
					else{
						$stringrt = "さらに" . ($rec_system["times_of_silver_rank"] - $numberpay["numberpay"]) . '回購入で' . $rec_system["sale_silver_rank"] . '%オフになります';
					}
				} else {
                	if($rec_system["times_of_gold_rank"] <= $numberpay["numberpay"]){
                    	$stringrt ="現在ブロンズランクです。<br/>おめでとうございます。次回よりゴールドランク(常時".$rec_system["sale_gold_rank"]."%オフ）にランクアップします。";
                	}
                	elseif($rec_system["times_of_silver_rank"] <= $numberpay["numberpay"]){
                    	$stringrt ="現在ブロンズランクです。<br/>おめでとうございます。次回よりシルバーランク(常時".$rec_system["sale_silver_rank"]."%オフ）にランクアップします。";
                	}
	                else{
                    	$stringrt ="現在ブロンズランクです。<br/>今月中に残り".($rec_system["times_of_silver_rank"] - $numberpay["numberpay"])."回のご購入で来月からシルバーランク（".$rec_system["sale_silver_rank"]."%オフ）になります。";
					}
				}
			}
		}
        else
        {
            $stringrt = '';
        }

		return $stringrt;
	}

    /**
     * Create a new user point
     *
     * @param $user
     * @param $point
     * @param $pay_id
     * @param $state
     * @param $expiry
     * @param $created
     * @param $point_log
     * @param $point_log_time
     * @param $isCreateLog
     * @return bool/string
     */
    function createUpoint($user, $point, $pay_id, $state = UPOINT_STATE['pending'], $expiry = '', $created = '', $point_log = 0, $point_log_time = '', $isCreateLog = true)
    {
        global $sql;

        if (!$u_rec = $sql->selectRecord("user", $user)) {
            return false;
        }

        if (!$u_rec["state"]) {
            return false;
        }

        if (empty($expiry)) {
            $s_rec = SystemUtil::getSystemParam();
            $expiry = date('Y-m-d 00:00:00', time() + 3600 * 24 * ($s_rec['upoint_expiration'] + 1));
        }

        if (empty($created)) {
            $created = date('Y-m-d H:i:s');
        }

        // create a user point
        $table    = 'upoints';
        $point_id = SystemUtil::getUniqId($table, false, true);
        $tmp_rec  = $sql->setData($table, null, "id", $point_id);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "user", $u_rec["id"]);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "created", $created);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "expiry", $expiry);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "point", $point);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "state", $state);
        $tmp_rec  = $sql->setData($table, $tmp_rec, "pay_id", $pay_id);

        $sql->addRecord($table, $tmp_rec);

        if (empty($point_log)) {
        	$point_log = $point;
        }

        if ($isCreateLog) {
            addPointToHistory($u_rec["id"], $pay_id, $point_id, $point_log, $state, $point_log_time);
        }

        return $point_id;
    }

	function updateUpoint($id, $state, $point = 0, $log_time = '', $change_point_log = false, $pay_id = null, $new = false)
	{
	    global $sql;

	    $table       = 'upoints';
        $user_point  = 0;
        $point_state = $state;

        if (!$upoint = $sql->selectRecord($table, $id)) {
	        return false;
        }

        if ($new) {
            createUpoint($upoint["user"], $point, $pay_id, UPOINT_STATE['used'], $upoint['expiry'], $upoint['created'], 0, $log_time, true);

            $update = $sql->setData($table, null, "point", $upoint['point'] - $point);
            $sql->updateRecord($table, $update, $upoint['id']);
		} else {
            if ($state == UPOINT_STATE['returned'] && ($upoint['state'] == UPOINT_STATE['available'] || $upoint['state'] == UPOINT_STATE['used'])) {
                $point_state = UPOINT_STATE['available'];
            }

            if ($upoint['state'] != $state) {
                $update = $sql->setData($table, null, "state", $point_state);

                if ($change_point_log) {
                    if ($upoint['state'] == UPOINT_STATE['used'] && $state == UPOINT_STATE['canceled']) {
                        $user_point = $upoint['point'];
                        $state      = UPOINT_STATE['available'];
                        $update     = $sql->setData($table, null, "state", $state);
                    } elseif ($upoint['state'] == UPOINT_STATE['pending'] && $state == UPOINT_STATE['available']) {
                        $user_point = $upoint['point'];
                        $s_rec      = SystemUtil::getSystemParam();
                        $expiry     = date('Y-m-d 00:00:00', time() + 3600 * 24 * ($s_rec['upoint_expiration'] + 1));
                        $update     = $sql->setData($table, $update, "expiry", $expiry);
                    }
                }

                if ($point > 0) {
                    if ($state == UPOINT_STATE['returned'] && $upoint['state'] == UPOINT_STATE['available']) {
                        $update = $sql->setData($table, $update, "point", $upoint['point'] + $point);
                    }
                } else {
                    $point = $upoint['point'];
                }

                $sql->updateRecord($table, $update, $id);

                //ポイント履歴を追加
                addPointToHistory($upoint["user"], $pay_id, $id, $point, $state);
            }

        }

        return $user_point;
	}

	function addPointToHistory($user_id, $pay_id, $point_id, $point, $state = UPOINT_STATE['pending'], $point_log_time = '')
	{
	    global $sql;

        if (empty(UPOINT_STATE['title'][$state])) {
            return null;
        }

        if (empty($point_log_time)) {
            $point_log_time = time();
        }

        $table   = "point_log";
        $tmp_rec = $sql->setData($table, null, "id", SystemUtil::getUniqId($table, false, true));
        $tmp_rec = $sql->setData($table, $tmp_rec, "user", $user_id);
        $tmp_rec = $sql->setData($table, $tmp_rec, "table_type", 'pay');
        $tmp_rec = $sql->setData($table, $tmp_rec, "table_id", $pay_id);
        $tmp_rec = $sql->setData($table, $tmp_rec, "upoint_id", $point_id);
        $tmp_rec = $sql->setData($table, $tmp_rec, "subject", UPOINT_STATE['title'][$state]);
        $tmp_rec = $sql->setData($table, $tmp_rec, "point", $point);
        $tmp_rec = $sql->setData($table, $tmp_rec, "state", $state);
        $tmp_rec = $sql->setData($table, $tmp_rec, "regist_unix", $point_log_time);

	    $sql->addRecord($table, $tmp_rec);
	}

	function metaDescription()
	{
		global $sql;
		$table = "page_category";

        $category_1 = Globals::get("category_1");
        $category_2 = Globals::get("category_2");
        $category_3 = Globals::get("category_3");

		if (empty($category_1)) {
			$where = $sql->setWhere($table, null, "id", "IN", '(select id from page_category where category_1 is null)');
		} else {
			$where = $sql->setWhere($table, null, "category_1", "=", $category_1);
		}
		if (empty($category_2)) {
			$where = $sql->setWhere($table, $where, "id", "IN", '(select id from page_category where category_2 is null)');
		} else {
			$where = $sql->setWhere($table, $where, "category_2", "=", $category_2);
		}
		if (empty($category_3)) {
			$where = $sql->setWhere($table, $where, "id", "IN", '(select id from page_category where category_3 is null)');
		} else {
			$where = $sql->setWhere($table, $where, "category_3", "=", $category_3);
		}

        $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

        if (!empty($result['meta_description'])) {
            return $result['meta_description'];
        } else {
            if (!empty($result['description'])) {
                return $result['description'];
            } else {
                return seoTag("description");
            }
        }
	}

	function updateOrderPrices()
	{
        $rec        = [];
        $rec["id"]  = Globals::get("pay_id");
        $sys        = SystemUtil::createSystem('pay');
        Globals::setSession("TOKEN_CODE", ['edit' => ['pay' => '']]);
        $token_code = SystemUtil::setTokenCode('edit', 'pay');
        Globals::setGet('design', 'pay');
        Globals::setGet('id', Globals::get("pay_id"));

        editPayItem();

        $sys->editComp('pay', $rec);

        HttpUtil::postLocation(sprintf('edit.php?type=pay&id=%s&design=pay', Globals::get('id')), [
            'page'       => 'check',
            'id'         => Globals::get('id'),
            'TOKEN_CODE' => $token_code,
        ]);
	}

	function editPayItem($get_price = false, $exclude_item = '')
	{
	    global $sql;

	    $cart        = [];

	    $table  = "pay_item";
	    $where  = $sql->setWhere($table, null, "pay", "=", Globals::get("id"));

	    if (!empty($exclude_item)) {
            $where  = $sql->setWhere($table, $where, 'item', 'NOT IN', $exclude_item);
        }

	    $order  = $sql->setOrder($table, null, "id", "ASC");
	    $result = $sql->getSelectResult($table, $where, $order);
	    $is_set_session = false;
        Globals::setSession("current_pay_item_id", null);

	    while ($rec = $sql->sql_fetch_assoc($result)) {
	        $item           = $rec["item"];
	        $pay_item_id    = $rec["id"];
	        $item_type      = $rec["item_type"];
	        $item_type_sub  = $rec["item_type_sub"];
	        $item_type_size = $rec["item_type_size"];
	        $cart_row       = $rec["item_row"];
            $product_type   = $rec["product_type"] == "blank" ? "bl" : "";

	        $cart_data = [
	            "item"           => $item,
	            "cart_row"       => $cart_row,
	            "pay_item_id"    => $pay_item_id,
	            "item_type"      => $item_type,
	            "item_type_sub"  => $item_type_sub,
	            "item_type_size" => $item_type_size,
	            "current_pay_item_id" => $pay_item_id,
				"product_type" => $product_type
	        ];

	        if ($get_price) {
                $cart_data['item_id'] = $rec['item'];
                $cart_data['cart_price'] = $rec['s_price'];
            }

	        $cart[$pay_item_id] = $cart_data;
	        if (!$is_set_session) {
                $is_set_session = true;
                Globals::setSession("current_pay_item_id", $rec["id"]);
            }
	    }

	    Globals::setSession("CART_ITEM", $cart);
	}

	function editPayPasralItem()
	{
		global $sql;

		$cart        = !empty(Globals::session("CART_ITEM_PASRAL")) ? Globals::session("CART_ITEM_PASRAL") : [];

		$table  = "pay_item";
		$where  = $sql->setWhere($table, null, "pay", "=", Globals::get("id"));
		$order  = $sql->setOrder($table, null, "id", "ASC");
		$result = $sql->getSelectResult($table, $where, $order);
		$is_set_session = false;
		Globals::setSession("current_pay_item_id", null);

		while ($rec = $sql->sql_fetch_assoc($result)) {
			$item           = $rec["item"];
			$pay_item_id    = $rec["id"];
			$cart_row       = $rec["item_row"];

			$cart_data = [
				"item"           => $item,
				"cart_row"       => $cart_row,
				"pay_item_id"    => $pay_item_id,
				"current_pay_item_id" => $pay_item_id,
				"product_type" =>  $rec["product_type"],
				"order_id" => $rec["pay"],
				"item_type"      => '',
				"item_type_sub"  => '',
				"item_type_size" => '',
			];

			$cart[$pay_item_id] = $cart_data;
			if (!$is_set_session) {
				$is_set_session = true;
				Globals::setSession("current_pay_item_id", $rec["id"]);
			}
		}

		Globals::setSession("CART_ITEM_PASRAL", $cart);
	}

	function getTockenBase($code,$state = false){

        global $sql;
        $userId = Globals::session("LOGIN_ID");

        $urlLink = "https://api.thebase.in/1/oauth/token";
        $postdata["grant_type"]= 'authorization_code';
        $postdata["client_id"]= BASE_CONFIG["client_id"];
        $postdata["client_secret"]= BASE_CONFIG["client_secret"];
        $postdata["code"]= $code;
        $postdata["redirect_uri"]= BASE_CONFIG["redirect_uri"];

        $ch = curl_init($urlLink);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $resulttoken = curl_exec($ch);
        curl_close($ch);
        $myArraytoken = json_decode($resulttoken, true);

		$query_update_item = "UPDATE user SET refresh_token_base = '".$myArraytoken["refresh_token"]."', created_at_token = '".time()."' WHERE id = '".$userId."'";
		$sql->rawQuery($query_update_item);

        return $myArraytoken["access_token"] ;
	}

	function getRefreshTockenBase($getOrder=false){
        global $sql;

        $userId = Globals::session("LOGIN_ID");
        if(!empty($userId)){
            $user = $sql->selectRecord('user', $userId);
			$Date = date('m/d/Y', $user["created_at_token"]);
            $dateCheck = date('Y-m-d', strtotime($Date. ' + 29 days'));
            $datecurrent = date('Y-m-d');

            if($datecurrent < $dateCheck){
                $urlLink = "https://api.thebase.in/1/oauth/token";
                $postdata["grant_type"]= 'refresh_token';
                $postdata["client_id"]= BASE_CONFIG["client_id"];
                $postdata["client_secret"]= BASE_CONFIG["client_secret"];
				$postdata["refresh_token"]= $user["refresh_token_base"];
                $postdata["redirect_uri"]= BASE_CONFIG["redirect_uri"];

                $ch = curl_init($urlLink);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                $resulttoken = curl_exec($ch);
                curl_close($ch);
                $myArraytoken = json_decode($resulttoken, true);

                $query_update_item = "UPDATE user SET refresh_token_base = '".$myArraytoken["refresh_token"]."', created_at_token = '".time()."' WHERE id = '".$userId."'";
                $sql->rawQuery($query_update_item);
                return $myArraytoken["access_token"] ;
            }
            else{
            	return null;
			}
		}
		else
			return null;

	}

	function addItemToBase($id, $title, $detail, $price, $stock, $visible,$itemImage,$code=false,$baseId=false)
	{
        global $sql;

        $refreshTokenBase = getRefreshTockenBase();

        if(!empty($refreshTokenBase)){
            $tokenBase = $refreshTokenBase;
		}
		else{
            $tokenBase = getTockenBase($code);
		}

        if(!empty($tokenBase)){
            $headers = array(
                'Authorization: Bearer ' .$tokenBase,
            );
            $postdata = array();
            $postdata["title"]= $title;
            $postdata["detail"]= $detail;
            $postdata["price"]= $price;
            $postdata["stock"]= $stock;
            $postdata["visible"]= $visible;
            $postdata["id"]= $id;
            $postdata["image_url"]= $itemImage;

            $item = $sql->selectRecord('item', $id);
            $imageaddbase = 1;
            if($itemImage == $item['item_preview2']){
                $imageaddbase = 2;
            } elseif($itemImage == $item['item_preview3']){
                $imageaddbase = 3;
            } elseif($itemImage == $item['item_preview4']){
                $imageaddbase = 4;
            }else{
                $imageaddbase = 1;
            }
			if(empty($baseId) || $baseId ==0){
                $url = "https://api.thebase.in/1/items/add";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                curl_close($ch);
                $myArray = json_decode($result, true);

                getShopBaseInfo($tokenBase);

                if(!empty($myArray["item"]["item_id"])){
                    $url = "https://api.thebase.in/1/items/add_image";
                    $postdata = array();
                    $postdata["item_id"]= $myArray["item"]["item_id"];
                    $postdata["image_no"]= 1;
                    $postdata["image_url"]= $itemImage;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    $resultImage = curl_exec($ch);
                    curl_close($ch);

                    $query_update_item = "UPDATE item SET registbase = '1', item_base_id = '".$myArray["item"]["item_id"]."', image_add_base = '.$imageaddbase.' WHERE id = '".$id."'";
                    $sql->rawQuery($query_update_item);

                    HttpUtil::location("/search.php?type=item&design=my");
                }
			}
			elseif (!empty($baseId)){
                $postdata["item_id"]= $baseId;
                $url = "https://api.thebase.in/1/items/edit";
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $result = curl_exec($ch);
                curl_close($ch);

                $urladdimage = "https://api.thebase.in/1/items/add_image";
                $postdataimage = array();
                $postdataimage["item_id"]= $baseId;
                $postdataimage["image_no"]= 1;
                $postdataimage["image_url"]= $itemImage;
                $ch = curl_init($urladdimage);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataimage);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                $resultImage = curl_exec($ch);
                curl_close($ch);

                $query_update_item = "UPDATE item SET image_add_base = '.$imageaddbase.' WHERE id = '".$id."'";
                $sql->rawQuery($query_update_item);

                getShopBaseInfo($tokenBase);

                HttpUtil::location("/search.php?type=item&design=my");

			}
		}

	}

	function getMyProfileUserBase($baseId){
        global $sql;
        $query = "SELECT * FROM base_orders WHERE base_order_id = '".$baseId."'";
        $result = $sql->sql_fetch_assoc($sql->rawQuery($query));
        return $result;
	}
	function updateBaseOrder($payId, $baseOrderId){
		global $sql;
        $table = "base_orders";
        $where = $sql->setWhere($table, null, 'base_order_id', '=', $baseOrderId);
        $update = $sql->setData($table, null, "pay", $payId);
        $update = $sql->setData($table, $update, "state", 1);
        $sql->updateRecordWhere($table, $update, $where);
	}

	function getShopBaseInfo($tokenBase, $updated = false){
        global $sql;
        $userId = Globals::session("LOGIN_ID");
        $query = "SELECT * FROM user WHERE id = '".$userId."'";
        $result = $sql->sql_fetch_assoc($sql->rawQuery($query));

        if(empty($result["base_shop_id"]) || empty($result["base_shop_link"]) || $updated == 1) {
            $apiLink = 'https://api.thebase.in/1/users/me';
            $headers = array(
                'Authorization: Bearer ' . $tokenBase,
            );
            $ch = curl_init($apiLink);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            $shopInfo = json_decode($result, true);
            if($shopInfo){
            	$table = "user";
                $update = $sql->setData($table, null, "base_shop_name", $shopInfo["user"]["shop_name"]);
                $update = $sql->setData($table, $update, "base_shop_link", $shopInfo["user"]["shop_url"]);
                $update = $sql->setData($table, $update, "base_shop_id", $shopInfo["user"]["shop_id"]);
                $sql->updateRecord($table, $update, $userId);
			}
        }
	}

	function loginLine()
	{
	    $data = [
	        'response_type' => 'code',
	        'client_id'     => LINE_CONFIG['client_id'],
	        'redirect_uri'  => LINE_CONFIG['redirect_uri'],
	        'state'         => 'line-code',
	        'scope'         => 'profile openid email',
	    ];

	    HttpUtil::location('https://access.line.me/oauth2/v2.1/authorize?' . http_build_query($data));
	}
	function loginInstagram()
	{
	    HttpUtil::location('https://api.instagram.com/oauth/authorize?client_id='.INSTA_CONFIG['client_id'].'&redirect_uri='.INSTA_CONFIG['redirect_uri'].'&scope=user_profile,user_media&response_type=code&state=insta-code');
	}

	function getLineAccessToken($code)
	{
	    $data = [
	        'grant_type'    => 'authorization_code',
	        'code'          => $code,
	        'redirect_uri'  => LINE_CONFIG['redirect_uri'],
	        'client_id'     => LINE_CONFIG['client_id'],
	        'client_secret' => LINE_CONFIG['client_secret'],
	        'state'         => 'line-access-token',
	    ];

	    $ch = curl_init('https://api.line.me/oauth2/v2.1/token');
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

	    $result = json_decode(curl_exec($ch), true);
	    curl_close($ch);

	    return $result;
	}

	function getInstagramAccessToken($code)
	{
	    $data = [
	        'grant_type'    => 'authorization_code',
	        'code'          => $code,
	        'redirect_uri'  => INSTA_CONFIG['redirect_uri'],
	        'client_id'     => INSTA_CONFIG['client_id'],
	        'client_secret' => INSTA_CONFIG['client_secret'],
	    ];

	    $ch = curl_init('https://api.instagram.com/oauth/access_token');
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

	    $result = json_decode(curl_exec($ch), true);
	    curl_close($ch);

	    return $result;
	}

	function getLineProfile($access_token)
	{
	    $ch = curl_init('https://api.line.me/v2/profile');

	    curl_setopt($ch, CURLOPT_HTTPHEADER, [
	        sprintf('Authorization: Bearer %s', $access_token),
	    ]);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);
	    curl_close($ch);

	    return json_decode($result);
	}

	function getInstagramProfile($access_token, $user_id)
	{
	    $ch = curl_init('https://graph.instagram.com/'.$user_id.'?fields=id,username&access_token='.$access_token);


	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	    $result = curl_exec($ch);
	    curl_close($ch);

	    return json_decode($result);
	}

	function getLineEmail($id_token)
	{
		$id_token = str_replace('{"typ":"JWT","alg":"HS256"}', '', $id_token);
		preg_match('/({[^}]*+})*/i', $id_token, $matches);

		if (!empty($matches)) {
			foreach ($matches as $match) {
                $info = json_decode($match, true);

                if (!empty($info) && !empty($info['email'])) {
                	return $info['email'];
                }
            }
		}

		return '';
	}

	function redirectToErrorPage($pay_id)
	{
        global $sql;
    	$table = 'pay';
        $rec = $sql->selectRecord ($table, $pay_id);
        if($rec["pay_type"] == 'card'){
            if(!empty($rec["charge_log"])){
                gmoFunc::sale2cancelEntry($rec["charge_log"]);
            }
        }
        if($rec["pay_type"] == 'amazon_pay'){
            refundAmazon($rec['pay_total'], $rec['amazon_capture_id']);
        }

        $update = $sql->setData($table, null, "pay_state", 2);
        $update = $sql->setData($table, $update, "memo", "エラーが発生したため自動キャンセルしました。念のため決済情報を確認してください。");
        $sql->updateRecord($table, $update, $rec["id"]);

        SystemUtil::errorPage();
	}

    function setup_auto_login($user_id, $login_type = 'admin')
    {
    	global $sql;
        $cookie_name     = 'remember_token';
        $remember_token = sha1(uniqid() . mt_rand(1, 999999999) . '_auto_login');
        $cookie_expire   = time() + 3600 * 24 * 7;
        $cookie_path     = '/';
        $cookie_domain   = $_SERVER['SERVER_NAME'];

        $update = $sql->setData($login_type, null, 'remember_token', $remember_token);
        $sql->updateRecord($login_type, $update, $user_id);

        setcookie($cookie_name, $remember_token, $cookie_expire, $cookie_path, $cookie_domain);
        setcookie('login_type', $login_type, $cookie_expire, $cookie_path, $cookie_domain);
    }

    function delete_remember_token($remember_token, $login_type)
    {
    	global $sql;

        $table  = $login_type;
        $where  = $sql->setWhere($table, null, "remember_token", "=", $remember_token);
        $update = $sql->setData($table, null, "remember_token", null);
        $sql->updateRecordWhere($table, $update, $where);

        unset($_COOKIE['remember_token'], $_COOKIE['login_type']);

        $cookie_expire   = time()-3600;
        $cookie_path     = '/';
        $cookie_domain   = $_SERVER['SERVER_NAME'];

        setcookie('remember_token', '', $cookie_expire, $cookie_path, $cookie_domain);
        setcookie('login_type', '', $cookie_expire, $cookie_path, $cookie_domain);
    }

    function check_auto_login()
    {
        if (empty(Globals::session('LOGIN_ID')) && !empty($_COOKIE['remember_token']) && !empty($_COOKIE['login_type'])) {
            global $sql;
            $remember_token = $_COOKIE['remember_token'];
            $login_type     = $_COOKIE['login_type'];

            $user = $sql->keySelectRecord($login_type, 'remember_token', $remember_token);

            if (!empty($user)) {
                Globals::setSession("LOGIN_TYPE", $login_type);
                Globals::setSession("LOGIN_ID", $user['id']);

                if ($login_type == 'admin') {
                    $LOGIN_SETTING = Globals::$LOGIN_SETTING;
                    Globals::setSession("ADMIN", $LOGIN_SETTING["admin"]);
                    Globals::setSession("LOGIN_NAME", $user["name"]);
                }

                setup_auto_login($user['id'], $login_type);
            }
        }
    }

	function getAddressUser()
	{
        global $sql;
        $data = [
            'add_num' => '',
            'address' => '',
            'house_num' => '',
            'address2' => '',
            'add_pre' => '',
        ];

        $user= $sql->selectRecord('user', Globals::session("LOGIN_ID"));
        if (!empty($user)) {
        	$house_num = substr(strstr($user['add_sub'], ' '), 1);
            if (!$house_num) {
                $house_num = '';
            }
            $data = [
                'add_num' => $user['add_num'],
                'address' => explode(' ', $user['add_sub'])[0],
                'house_num' => $house_num,
                'address2' => $user['add_sub2'],
                'add_pre' => $user['add_pre'],
            ];
        }

        return $data;
	}

	function getUserInfo($pay)
	{
		global $sql;

        $pay['id'] = '';
		unset($pay['regist_unix']);
        $shop = $sql->selectRecord('personal_shop_info', $pay['design_store_id']);

        if (!empty($shop)) {
            $user = $sql->selectRecord('user', $shop['user']);

            if (!empty($user)) {
				foreach ($user as $key => $value) {
					if (!empty($pay[$key])) {
						$pay["shop_user_{$key}"] = $pay[$key];
					}

					$pay[$key] = $value;
				}
            }
        }

		return $pay;
	}

	function getShopItem($shop_id, $item_id)
	{
    	global $sql;
    	$table = 'shop_items';

    	$where = $sql->setWhere($table, null, 'id', '=', $shop_id);
    	$where = $sql->setWhere($table, $where, 'state', '=', 1);
    	$where = $sql->setWhere($table, $where, 'item_id', '=', $item_id);

    	return $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));
	}

    function getShopInfo($shop_id)
    {
        global $sql;

        $shop = $sql->selectRecord('personal_shop_info', $shop_id);

        if (!empty($shop)) {
        	if (!empty($shop)) {
        		$logo = sprintf('<img class="logo lazyload" data-src="%s" alt="logo">', $shop['logo']);
	        } else {
        		$logo = $shop['shop_name'];
	        }
            $shop_info = [
                'name' => $shop['shop_name'],
                'post_code' => $shop['add_num'],
                'address' => $shop['address'],
                'add_pre' => $shop['add_pre'],
                'address_2' => $shop['address2'],
                'url' => $shop['url'],
                'logo' => $logo
            ];
        } else {
            $shop_info = [
				'name' => '',
				'post_code' => '',
				'address' => '',
				'address_2' => '',
				'url' => '',
				'logo' => '',
            ];
        }

        return $shop_info;
    }

	function getTaxRate($regist_unix= false)
	{
		global $sql;
        $date = new DateTime("now", new DateTimeZone('Asia/Tokyo') );
        $time_now = $date->format('Y-m-d H:i:s');
        if(empty($regist_unix)){
            $regist_unix = $time_now;
		}else{
            $regist_unix = date('Y-m-d H:i:s', $regist_unix);
        }

        global $sql;
        $table = 'system_parameters';
        $tax_use = 0.08;

        $where = $sql->setWhere($table, null, 'validStart ', '<=', $regist_unix);
        $where = $sql->setWhere($table, $where, 'validEnd', '>', $regist_unix);
        $where = $sql->setWhere($table, $where, 'kind', '=', 'tax_rate');

        $rec_result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

        if(!empty($rec_result["value"])){
            $tax_use = $rec_result["value"];
		}
		return (float)$tax_use;
	}

	function check_post_news()
	{
        global $sql;
        $check_news = '';
        if(empty(Globals::session("LOGIN_ID"))){
            $check_news = 0;
        }else{
            $rec = $sql->selectRecord('user', Globals::session("LOGIN_ID"));
            if (!empty($rec)){
                $check_news = $rec["post_news_check"];
            }
        }

        return $check_news;
	}

	function same_day_master_item_type()
	{
		global $sql;
		$table = 'master_item_type';

		$where = $sql->setWhere($table, null, 'state', "=", 1);
		$where = $sql->setWhere($table, $where, 'flag_same_day', "=", 1);
		$array_same_day = [];

		$result = $sql->getSelectResult($table, $where);

		while($rec = $sql->sql_fetch_assoc($result)){
			$array_same_day[] = $rec['id'];
		}
		return $array_same_day;
	}

    function updateCartValue($data, $item_data, $itsu_rec, $item_price, $price_type = null) {

        if(!empty($price_type) && $price_type == 2){
            // get new price
            $result = getItemPrice($item_data['item_type'], $item_data['item_type_sub']);
            $item_price = $result['item_price'];
            $itsu_rec['cost1'] = $result['cost1'];
            $itsu_rec['cost2'] = $result['cost2'];
            $itsu_rec['cost3'] = $result['cost3'];

        }
        $has_embroidery = false;

        if (!empty($item_data['objEmbroider'])) {
            $embroidery_sides = json_decode($item_data['objEmbroider'], true);
            for ($i = 1; $i <= 4; $i++) {
                $side = $i;
                $image_side = sprintf('image_path%s', $i);
                $side_name  = sprintf('sideface%s', $i);
                $side_value = $item_data[$side_name];

                if ($i == 4) {
                    $side = 3;
                }

                if (!empty($side_value) && ($side_value == EMBROIDERY || $side_value == PRINT_EMBROIDERY)) {
                    foreach ($embroidery_sides[$i - 1]['e'] as $key => $embroidery) {
                        if (empty($embroidery)) {
                            unset($embroidery_sides[$i - 1]['e'][$key]);
                        }
                    }

                    getEmbroideryPrice($embroidery_sides[$i - 1]['e'], $item_price, EMBROIDERY_PRICE[$i]);

                    if (!empty($embroidery_sides[$i - 1]['e'])) {
                        $has_embroidery = true;
                        $data['embroidery_print']['embroidery'][$i] = $embroidery_sides[$i - 1]['e'];
                    }

                    if (!empty($item_data[$image_side]) && $side_value == PRINT_EMBROIDERY) {
                        $item_price += $itsu_rec[sprintf('cost%s', $side)];
                        $data['embroidery_print']['print'][$i] = $itsu_rec[sprintf('cost%s', $side)];
                    }
                } else {
                    if (!empty($item_data[$image_side])) {
                        $item_price += $itsu_rec[sprintf('cost%s', $side)];
                        $data['embroidery_print']['print'][$i] = $itsu_rec[sprintf('cost%s', $side)];
                    }
                }
            }
        } else {
            if (!empty($item_data['embroidery_print'])) {
                $embroidery_print = json_decode($item_data['embroidery_print'], true);

                for ($i = 1; $i <= 4; $i++) {
                    $side = $i;
                    $image_side = sprintf('image_path%s', $i);
                    $side_name  = sprintf('sideface%s', $i);
                    $item_data[$side_name] = '';

                    if ($i == 4) {
                        $side = 3;
                    }

                    if (!empty($embroidery_print['print'][$i])) {
                        $item_data[$side_name] .= 'p';
                        $item_price += $itsu_rec[sprintf('cost%s', $side)];

                        if (empty($item_data[$image_side])) {
                            $item_data[$image_side] = true;
                        }
                    }

                    if (!empty($embroidery_print['embroidery'][$i])) {
                        foreach ($embroidery_print['embroidery'][$i] as $embroidery_side) {
                            if (!empty($embroidery_side)) {
                                $has_embroidery = true;
                                $item_price += EMBROIDERY_PRICE[$i];
                            }
                        }

                        if ($has_embroidery) {
                            $item_data[$side_name] .= 'e';
                        }
                    }
                }

                if ($has_embroidery) {
                    $data['embroidery_print'] = $embroidery_print;
                }
            }
        }

        if ($has_embroidery) {
            $data['embroidery_print'] = json_encode($data['embroidery_print']);
            $data['has_embroidery'] = 1;
        } else {
            unset($data['embroidery_print']);
        }

        return [$data, $item_price];
    }

    // check if the user has wishlist yet? If not, then create new
    function checkUserWishList($user)
    {
        global $sql;
        $wish_list = 'wish_list';

        $where = $sql->setWhere($wish_list, null, "user", "=", $user);
        $where = $sql->setWhere($wish_list, $where, "name", "=", "デフォルトリスト");
        $rec = $sql->sql_fetch_assoc($sql->getSelectResult($wish_list, $where));

        if (empty($rec)) {
            $id = SystemUtil::getUniqId($wish_list, false, true);
            $regist = $sql->setData($wish_list, null, "id", $id);
            $regist = $sql->setData($wish_list, $regist, "name", 'デフォルトリスト');
            $regist = $sql->setData($wish_list, $regist, "user", $user);
            $regist = $sql->setData($wish_list, $regist, "status", 0);
            $regist = $sql->setData($wish_list, $regist, "regist_unix", time());

            $sql->addRecord($wish_list, $regist);

            return $id;
        } else {
            return $rec['id'];
        }
    }

    function findItemWishList($item)
    {
        global $sql;

        $clume = $sql->setClume('wish_list', null, 'user');
        $clume = $sql->setClume('item_wish_list', $clume, 'id');
        $clume = $sql->setClume('item_wish_list', $clume, 'item');
        $clume = $sql->setClume('item_wish_list', $clume, 'wish_list');
        $inner_join = $sql->setInnerJoin('wish_list', 'item_wish_list', 'wish_list', 'wish_list', 'id');
        $where = $sql->setWhere('item_wish_list', null, 'item', '=', $item);
        $where = $sql->setWhere('wish_list', $where, 'user', '=', Globals::session('LOGIN_ID'));

        $rec = $sql->sql_fetch_assoc($sql->getSelectResult("item_wish_list", $where, null, null, $clume, null, $inner_join));

		return $rec;
	}

    function getItemTypeQuery(){
        $select = 'SELECT
											master_item_type.id,
											master_item_type.name,
											master_item_type.order,
											master_item_type.material,
											master_item_type.color_total,
											master_item_type.size,
											master_item_type.item_price,
											master_item_type.sale_price,
											master_item_type.item_code_nominal,
											master_item_type.maker,
											master_item_type.state,
											master_item_type.preview_url,
											master_item_type.tool_price,
											master_item_type.thickness,
											master_item_type.special_draw,
											master_item_type.flag_dry,
											master_item_type.sales_status,
											master_item_type.order_suspended,
											master_item_type.is_discount,
											master_item_type.discount_coupon,
											master_item_type_page.item_text,
                                            master_item_type_page.item_text_detail,
											master_item_type_page.preview_image,
											master_item_type_page.print_method_id,
											master_categories.title,
											GROUP_CONCAT(master_item_type_sub_sides.title) as \'titles\',
                                            master_item_type.tool_price * master_item_type.sale_price * 1.1 as \'web_price\'';
        $innerJoin = 'FROM
										   master_item_type
										   LEFT JOIN master_categories ON master_item_type.category_id = master_categories.id
										   LEFT JOIN master_item_type_page ON master_item_type.id = master_item_type_page.item_type
										   LEFT JOIN master_item_type_sub ON master_item_type.id = master_item_type_sub.item_type
										   LEFT JOIN master_item_type_sub_sides ON master_item_type_sub_sides.color_id = master_item_type_sub.id ';


        $where = 'WHERE (master_item_type.state = 1) AND (master_categories.is_deleted = 0)
										AND master_item_type.id IN ( ( SELECT item_type FROM master_item_type_page ) ) ';

        $groupBy = 'GROUP BY master_item_type.id ';
        $orderBy = '';

        if (!empty(Globals::get('category'))){
            $where .= 'AND master_item_type.category_id = '.Globals::get('category').' ';
        }

        if (!empty(Globals::get('price')) && Globals::get('price') == 'lowest' ){
            $orderBy .= 'ORDER BY web_price ASC';
        } elseif (!empty(Globals::get('price')) && Globals::get('price') == 'highest' ){
            $orderBy .= 'ORDER BY web_price DESC';
        } else {
            $orderBy .= 'ORDER BY master_item_type_page.order ASC';
        }

        return $condition = ['select' => $select,
            'innerJoin' => $innerJoin,
            'where' => $where,
            'groupBy' => $groupBy,
            'orderBy' => $orderBy];
    }

	function getItemType($item_type_re = false)
	{
		global $sql;

		$item_type = array();

        $condition = getItemTypeQuery();
        $where = $condition['where'];
        $select = $condition['select'];
        $innerJoin = $condition['innerJoin'];
        $groupBy = $condition['groupBy'];
        $orderBy = $condition['orderBy'];

		$query = $select . $innerJoin . $where . $groupBy . $orderBy;

		$result = $sql->rawQuery($query);

		if(!empty($item_type_re)){
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $rec['titles'] = getSideNames($rec['titles']);
            	if($item_type_re == $rec['id']){
					return $rec;
				}
            }
		}
		else{
            while ($rec = $sql->sql_fetch_assoc($result)) {
                $rec['titles'] = getSideNames($rec['titles']);
                $item_type[$rec['id']] = $rec;
            }
		}

		return $item_type;

	}

	function getLiMasterItemWeb()
	{
		global $sql;
		$data = array();
		$category = array();
		$sub_category = array();
		$icon = array();
		$mapping = array();

		$table_category = 'master_item_web_categories';
		$where_category = $sql->setWhere($table_category,null,'state','=',1);
		$order_category = $sql->setOrder($table_category,null,'category_order');
		$clume_category = $sql->setClume($table_category,null,'id',null,'web_id');
		$clume_category = $sql->setClume($table_category,$clume_category,'name',null,'web');

		$result_category = $sql->getSelectResult($table_category, $where_category, $order_category,null,$clume_category);

		while($rec_category = $sql->sql_fetch_assoc($result_category))
		{
			$category[$rec_category['web_id']] = $rec_category['web'];
		}

		$table_sub_category = 'master_item_web_sub_categories';
		$where_sub_category = $sql->setWhere($table_sub_category,null,'state','=',0);
		$order_sub_category = $sql->setOrder($table_sub_category,null,'category_order');
		$clume_sub_category = $sql->setClume($table_sub_category,null,'id',null,'web_sub_id');
		$clume_sub_category = $sql->setClume($table_sub_category,$clume_sub_category,'name',null,'web_sub');
		$clume_sub_category = $sql->setClume($table_sub_category,$clume_sub_category,'icon');
		$clume_sub_category = $sql->setClume($table_sub_category,$clume_sub_category,'parent');

		$result_sub_category = $sql->getSelectResult($table_sub_category, $where_sub_category, $order_sub_category,null,$clume_sub_category);

		while($rec_sub_category = $sql->sql_fetch_assoc($result_sub_category))
		{
			$sub_category[$rec_sub_category['web_sub_id']] = $rec_sub_category['web_sub'];
			$icon[$rec_sub_category['web_sub_id']] = $rec_sub_category['icon'];

			if(!empty($category[$rec_sub_category['parent']])){
				$mapping[$rec_sub_category['parent']][$rec_sub_category['web_sub_id']] = $rec_sub_category['web_sub'];
			}
		}

		foreach ($category as $key => $value) {
		    if (!isset($mapping[$key])) {
                $mapping[$key] = null;
            }

			$data_order[$key] = $mapping[$key];
			$data[$value] = $mapping[$key];
		}

		Globals::setGet('category_data',$data);
		Globals::setGet('category_data_order',$data_order);
		Globals::setGet('data_category',$category);
		Globals::setGet('data_sub_category',$sub_category);
		Globals::setGet('icon',$icon);
		return $data;

    }

	function getPreviewSide($item_sub)
	{
		global $sql;
		$sides = array();

		$clume = $sql->setClume('master_item_type_sub_sides', null, 'side_name');
		$clume = $sql->setClume('master_item_type_sub_sides', $clume, 'title');
		$clume = $sql->setClume('master_item_type_sub_sides', $clume, 'preview_url');
		$clume = $sql->setClume('master_item_type_sub', $clume, 'cost1');
		$clume = $sql->setClume('master_item_type_sub', $clume, 'cost2');
		$clume = $sql->setClume('master_item_type_sub', $clume, 'cost3');
		$clume = $sql->setClume('master_item_type_sub', $clume, 'thumbnail_url');
		$clume = $sql->setClume('master_item_type_sub', $clume, 'item_type');

		$where = $sql->setWhere('master_item_type_sub', null, 'id', '=', $item_sub);
		$where = $sql->setWhere('master_item_type_sub_sides', $where, 'state', '=', 1);

		$inner_join = $sql->setInnerJoin('master_item_type_sub', 'master_item_type_sub_sides', 'color_id', 'master_item_type_sub', 'id');

		$result = $sql->getSelectResult('master_item_type_sub_sides', $where, null, null, $clume, null, $inner_join);

		while ($rec = $sql->sql_fetch_assoc($result)) {

			if ($rec['side_name'] == '1') {
				$printPrice = intval($rec['cost1']);
			} elseif ($rec['side_name'] == '2') {
				$printPrice = intval($rec['cost2']);
			} else {
				$printPrice = intval($rec['cost3']);
			}
			$sides[$rec['side_name']] = $rec;
			$sides[$rec['side_name']]['item_sub'] = $item_sub;
			$sides[$rec['side_name']]['printPrice'] = $printPrice;
		}

		return $sides;
	}

	function calculatePriceProductReport($price,$total,$plus = true)
	{
		if ($plus == true) {

			if (!empty(Globals::session('PRICE_REPORT'))) {

				$price_session = Globals::session('PRICE_REPORT');

				$price_report['price_total'] = $price_session['price_total'] + ($price * $total) ;
				$price_report['total'] = $price_session['total'] + $total;
			} else {

				$price_report['price_total'] = $price;
				$price_report['total'] = $total;
			}
		} else {

				$price_session = Globals::session('PRICE_REPORT');

				$price_report['price_total'] = $price_session['price_total'] - ($price * $total) ;
				$price_report['total'] = $price_session['total'] - $total;
		}
		$price_report = discountByTotal($price_report['price_total'], $price_report['total']);

		Globals::setSession('PRICE_REPORT', $price_report);

		return $price_report;
	}

	function discountByTotal($price,$total)
	{
        list($next, $discount, $discount_par, $next_discount_par) = getDisCount($total, $price);

		return array("next" => $next, "discount" => $discount, "discount_par" => $discount_par, "next_discount_par" => $next_discount_par, "price_total" => $price, "total" => $total, "price_discount" => $price - $discount);
	}

function updateOrderPayment($rec)
{
    if (checkPayStatus($rec['id'], $rec['pay_type'], $rec)) {
        changePayPay($rec["id"], 1);
    } elseif (in_array($rec["pay_type"], ['sb', 'docomo', 'au', 'linepay'])) {
        global $sql;

        $table  = 'pay';
        $update = $sql->setData($table, null, "pay_state", 0);
        $update = $sql->setData($table, $update, "conf_datetime", '');
        $sql->updateRecord($table, $update, $rec["id"]);
    }
}

// Function to get the client IP address
function get_client_ip() {
    if (getenv('HTTP_X_REAL_IP')) {
        $ip_address = getenv('HTTP_X_REAL_IP');
    } elseif (getenv('HTTP_CLIENT_IP')) {
        $ip_address = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDD_FOR')) {
        $ip_address = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ip_address = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ip_address = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ip_address = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ip_address = getenv('REMOTE_ADDR');
    } else {
        $ip_address = '';
    }

    return explode('/', $ip_address)[0];
}

function setCareerNetworkType() {
    $ip_address = get_client_ip();
    Globals::setSession('career_ip', $ip_address);

    if (!empty($ip_address)) {
        $careers = [
            'docomo' => 'ドコモ払い',
            'au'     => 'ａｕかんたん決済',
            'sb'     => 'ソフトバンクまとめて支払い',
        ];

        $career = getNetwork($ip_address);

        if (array_key_exists($career, $careers)) {
            Globals::setSession('career_type', $career);
            Globals::setSession('career_description', $careers[$career]);
        } else {
            Globals::setSession('career_type', '');
            Globals::setSession('career_description', '');
        }
    } else {
        Globals::setSession('career_type', '');
        Globals::setSession('career_description', '');
    }
}

function getNetwork($ip) {
    require_once 'vendor/autoload.php';

    $network = '';
    $client = new \GuzzleHttp\Client();
    $url    = sprintf('https://api.ipdata.co/%s/carrier?api-key=531692b0c38bfae370f52107caefa7017efe194a5e53f5bbf5cabf57', $ip);

    try {
        $response = $client->request('GET', $url);
        $network  = strtolower(json_decode($response->getBody()->getContents(), true)['name']);

        if (empty($network)) {
            global $mobile_type;

            if ($mobile_type == MobileUtil::$TYPE_DOCOMO) {
                $network = 'docomo';
            } elseif ($mobile_type == MobileUtil::$TYPE_SOFTBANK) {
                $network = 'sb';
            } elseif ($mobile_type == MobileUtil::$TYPE_AU) {
                $network = 'au';
            }
        }

        if (strpos($network, 'docomo') !== false) {
            $network = 'docomo';
        } elseif (strpos($network, 'softbank') !== false) {
            $network = 'sb';
        } elseif (strpos($network, 'au') !== false) {
            $network = 'au';
        }
    } catch (Exception $exception) {
        // Nothing
    }

    if (empty($network) && MobileUtil::auIpCheck($ip)) {
        return 'au';
    }

    return $network;
}

function getPayInfo($order_id)
{
    global $sql;

    $pay_session = $sql->keySelectRecord('pay_session', 'pay', $order_id);

    if (!empty($pay_session)) {

        $session = unserialize($pay_session['session']);
        foreach ($session as $key => $session_item) {
            Globals::setSession($key, $session_item);
        }

        return Globals::session('PAY_RAKUTEN');
    }

    return null;
}

function addBlankItem($contents, $item) {
	global $sql;

	$table = 'item';

	$where = $sql->setWhere($table, null, "name", "=", $item['name']);
	$where = $sql->setWhere($table, $where, "product_type", "=", 'bl');
	$rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

	if (empty($rec)) {
		$id = SystemUtil::getUniqId($table, false, true);
		$regist = $sql->setData($table, null, "id", $id);
		$regist = $sql->setData($table, $regist, "name", $item['name']);
		$regist = $sql->setData($table, $regist, "item_type", $contents['item_type']);
		$regist = $sql->setData($table, $regist, "item_type_sub", $contents['item_type_sub']);
		$regist = $sql->setData($table, $regist, "item_type_size", $contents['item_type_size']);
		$regist = $sql->setData($table, $regist, "item_text", $contents['item_name']);
		$regist = $sql->setData($table, $regist, "item_preview1", $contents['image_pre1']);
		$regist = $sql->setData($table, $regist, "product_type", 'bl');
		$regist = $sql->setData($table, $regist, "state", 1);
		$regist = $sql->setData($table, $regist, "regist_unix", time());

		$sql->addRecord($table, $regist);

		return $id;
	} else {
		return $rec['id'];
	}
}

function getCachedContent($field, $name, $is_new = false)
{
    global $sql;

    if ($is_new) {
        return null;
    }

    $table = 'cached_content';
    $where = $sql->setWhere($table, null, 'name', "=", $name);
    $where = $sql->setWhere($table, $where, 'updated_at', "=", date('Y-m-d'));

    $cached_content = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

    if (!empty($cached_content[$field])) {
        return $cached_content[$field];
    }

    return null;
}

function deletePaySession($order_id)
{
    try {
        global $sql;
        $where_delete = $sql->setWhere('pay_session', null, 'pay', '=', $order_id);
        $sql->deleteRecordWhere('pay_session', $where_delete);
    } catch (Exception $exception) {
        // nothing
    }
}

function givePointReviews($id, $state, $order = null)
{
    global $sql;
    $pay_table = 'pay';
    $time = time();
    // get data review
    $review = $sql->selectRecord('voices', $id);

    //change public review
	$update = $sql->setData('voices', null, 'approve', $state);
	if ($state == 2 || $state == 0) {
		$update = $sql->setData('voices', $update, 'public', 0);
	} else {
		$update = $sql->setData('voices', $update, 'public', $state);

		if ($review['gave_point'] == 0) {
			$update = $sql->setData('voices', $update, 'gave_point', 1);
		}
	}
    $update = $sql->setData('voices', $update, 'edit_unix', $time);
    $sql->updateRecord('voices', $update, $review['id']);

	if ($review['gave_point'] == 0) {

		//check public = 1 give point into user create review
		if ($state == 1) {

			// get user give points
			if (!$user = $sql->selectRecord('user', $review['user'])) return false;

			if (empty($order)) {
				$pay_where = $sql->setWhere($pay_table, null, "delivery_state", '=', 1);
				$pay_where = $sql->setWhere($pay_table, $pay_where, "pay_state", "=", 1);
				$pay_where = $sql->setWhere($pay_table, $pay_where, "name_ruby", "=", 'granted point');
				$pay_where = $sql->setWhere($pay_table, $pay_where, "name", "=", 'granted point');
				$pay_where = $sql->setWhere($pay_table, $pay_where, "price", "=", 0);
				$pay_where = $sql->setWhere($pay_table, $pay_where, "pay_num", "=", '-');
				$order = $sql->setOrder($pay_table, null, "regist_unix", "ASC");
				$pay_point = $sql->sql_fetch_assoc($sql->getSelectResult($pay_table, $pay_where, $order, [0, 1]));

				$order = $pay_point['id'];
			}

			$s_rec = SystemUtil::getSystemParam();
			$expiry = date('Y-m-d 00:00:00', $time + 3600 * 24 * ($s_rec['upoint_expiration'] + 1));

			createUpoint($user['id'], $s_rec['point_approve_voice'], $order, UPOINT_STATE['available'], $expiry);

			//update point current
			$user_point = 0;
			if ($point_rec = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT SUM(point) as point FROM upoints WHERE `user` = "%s" AND state = %s', $user['id'], UPOINT_STATE['available'])))) {
				$user_point = $point_rec['point'];
			}
			$update = $sql->setData('user', null, 'point', $user_point);
			$sql->updateRecord('user', $update, $user['id']);

			//send mail user
			$user["grant_point"] = $s_rec['point_approve_voice'];
			$user["point"] = $update["point"];
			mail_templateFunc::sendMail("user", 'grant_point_review', $user["mail"], $user);
		}
	}

    return $order;
}

	function changeStateItemStore($id, $state)
	{
		global $sql;

		$table = "my_item_store";
		if(!$rec = $sql->selectRecord($table, $id)) return;

		$update = $sql->setData($table, null, "admin_state", $state);
		$sql->updateRecord($table, $update, $rec["id"]);
	}

	function updateShopItemSort($shop_id, $user)
	{
		if (empty($shop_id) || empty($user)) {
			return null;
		}

		global $sql;

		$shop_item_query = sprintf("SELECT S.*,shop_items.sort, shop_items.id as shop_id from shop_items INNER JOIN ( SELECT 'item' as shop_item_type, item.id, regist_unix, (SELECT IF(item.item_preview1 != '', IF(item.item_preview2 != '', IF(item.item_preview3 != '', IF(item.item_preview4 != '', MAX(master_item_type.item_price) + item.price - item.item_price + master_item_type_sub.cost1 + master_item_type_sub.cost2 + master_item_type_sub.cost3 * 2, MAX(master_item_type.item_price) + item.price - item.item_price + master_item_type_sub.cost1 + master_item_type_sub.cost2 + master_item_type_sub.cost3), MAX(master_item_type.item_price) + item.price - item.item_price + master_item_type_sub.cost1 + master_item_type_sub.cost2), MAX(master_item_type.item_price) + item.price - item.item_price + master_item_type_sub.cost1), MAX(master_item_type.item_price) + item.price - item.item_price) FROM master_item_type JOIN master_item_type_sub ON master_item_type.id = master_item_type_sub.item_type WHERE master_item_type.id = item.item_type AND master_item_type_sub.id = item.item_type_sub GROUP BY master_item_type.id) as price,name from item WHERE regist_unix > 0 and user = '%s' and state = 1 UNION SELECT 'my_item_store' as shop_item_type, my_item_store.id, UNIX_TIMESTAMP(created_at) as regist_unix, price,name from my_item_store where admin_state = 1 and state = 1 and user = '%s' ) S on shop_items.shop_item_type = S.shop_item_type And shop_items.item_id = S.id AND shop_items.state = 1 WHERE shop_items.shop_id = '%s' ORDER BY shop_items.sort ASC, regist_unix DESC",
			$user, $user, $shop_id);

		$sort           = 1;
		$unsorted_items = [];
		$shop_items     = $sql->rawQuery($shop_item_query);

		while ($shop_item = $sql->sql_fetch_assoc($shop_items)) {
			if (empty($shop_item['sort'])) {
				$unsorted_items[] = $shop_item;
				continue;
			}

			$sql->updateRecord('shop_items', array('sort' => $sort), $shop_item['shop_id']);

			$sort++;
		}

		if (!empty($unsorted_items)) {
			foreach ($unsorted_items as $shop_item) {
				$sql->updateRecord('shop_items', array('sort' => $sort), $shop_item['shop_id']);

				$sort++;
			}
		}
	}

	function updateSortItem($shop_id, $current_sort, $new_sort)
	{
		global $sql;

		if ($current_sort > $new_sort) {
			$update_sort_query = sprintf('UPDATE shop_items SET sort = sort + 1 WHERE shop_id = "%s" AND sort >= "%s";', $shop_id, $new_sort);
		} else {
			$update_sort_query = sprintf('UPDATE shop_items SET sort = sort - 1 WHERE shop_id = "%s" AND sort <= "%s";', $shop_id, $new_sort);
		}

		$sql->rawQuery($update_sort_query);
	}

// Function get data from api pasral
function getDataApiPasral($design_id_pasral, $hash_code_pasral)
{
	$url = ApiConfig::API_PASRAL."/api/design/".$design_id_pasral."?session=".$hash_code_pasral;
	$ch = curl_init( $url );
	if($ch == false) {
		die('Item not exist !');
	}else {
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HTTPHEADER => array('Content-type: application/json')
		);
		curl_setopt_array( $ch, $options );

		$result_pasral = json_decode(curl_exec($ch), true);
	}
	return $result_pasral;
}
	function getDataPriceItem()
	{
		global $sql;

		$list_blank_item = array();

		$query_list_price_blank_item = $sql->rawQuery("SELECT item_type, MIN(price) as min_price FROM master_blank_item_price WHERE price > 0 GROUP BY item_type");

		while ($blank_item = $sql->sql_fetch_assoc($query_list_price_blank_item)) {

				$list_blank_item[$blank_item['item_type']] = (int)$blank_item['min_price'];
		}

		return $list_blank_item;

	}

	function getTextNumberBlank($itemId)
	{
		global $sql;
		global $CATEGORY_BLANK;
		$table = 'item_web_categories';
        $text = '';

		//check text
		$cat_clume = $sql->setClume($table, null, 'category');
		$cat_where = $sql->setWhere($table, null, 'item_type', '=', $itemId);
		$cat_group = $sql->setGroup($table, null, 'category');
		$cat_result = $sql->getSelectResult($table, $cat_where, null, null, $cat_clume, $cat_group);
		while ($rec = $sql->sql_fetch_assoc($cat_result)) {
			if (array_key_exists($rec['category'], $CATEGORY_BLANK)) {
				$text = '';
			}
		}

		return $text;
	}

	function sp_replace_send_date(&$buffer)
	{
		global $device_path;

		if ($device_path == 'smart/') {
            $buffer = replace_send_date($buffer);
        }
	}

	/**
	 * Get list shop
	 *
	 * @return array
	 */
	function getListShop()
	{
		global $sql;

        if (Globals::getItems('shops') === null) {
            $shops = [];
            $table = 'personal_shop_info';
            $where = $sql->setWhere($table, null, 'user', "=", Globals::session("LOGIN_ID"));
            $result = $sql->getSelectResult($table, $where);

            if ($result->num_rows > 0) {
                while ($rec = $sql->sql_fetch_assoc($result)) {
                    $shops[] = $rec;
                }
            }

            Globals::setItems($shops, 'shops');
        }

		return Globals::getItems('shops');
	}

    /**
     * @param int $status_login
     * @param string $user_id
     * @throws Exception
     */
	function changeStatusLogin($status_login = 1, $user_id = '')
	{
		global $sql;

        $rec = null;
        $session = [];
		$table = 'users_sessions';
		$session['session'] = Globals::session('DRAW_TOOL_SESSION');

        if (empty($session['session'])) {
            $curlChannel = curl_init();
            curl_setopt_array($curlChannel, array(
                CURLOPT_URL => ApiConfig::HOST . '/session/web',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
            ));
            $response = curl_exec($curlChannel);
            curl_close($curlChannel);

            $session = json_decode($response, true);

            if (empty($session)) {
                throw new Exception('Service is not available now. Please, try later. - ' . ApiConfig::HOST . '/session/web - ' . $session);
            }

            Globals::setSession('DRAW_TOOL_SESSION', $session['session']);
        }

        if (!empty($session['session'])) {
            $where = $sql->setWhere($table, null, 'token', '=', $session['session']);

            if ($status_login !== null) {
                $rec = $sql->setData($table, $rec, 'status_login', $status_login);
            }

            if (!empty($user_id)) {
                $rec = $sql->setData($table, $rec, 'user_id', $user_id);
            }

            $sql->updateRecordWhere($table, $rec, $where);
		}
	}

	function sendPasralEmail()
	{
		if (Extension::checkCartPasral()) {
            MailUtil::send('パワーストーンの注文が入りました。', '管理画面を確認してください', 'info@up-t.jp', 'scp-jimuin@maruiorimono.jp');
            MailUtil::send('パワーストーンの注文が入りました。', '管理画面を確認してください', 'info@up-t.jp', 'info@up-t.jp');
		}
	}

	function isMarket() {
        if (Globals::session('LOGIN_TYPE') != 'admin') {
            $current_script = str_replace('/', '', $_SERVER['SCRIPT_NAME']);

            $markets = [
                'info.php' => [
                    'type' => [
                        'item'
                    ]
                ]
            ];

            foreach ($markets as $script => $types) {
                if ($script == $current_script) {
                    foreach ($types as $type => $pages) {
                        if (in_array(Globals::get($type), $pages)) {
                            return true;
                        }
                    }
                }
            }
        }

		return false;
	}

	function getEmbroideryPrice($embroideries, &$price, $embroidery_price)
	{
        if (is_array($embroideries) || is_object($embroideries)) {
            foreach ($embroideries as $embroidery_side) {
                if ($embroidery_side >= 1) {
                    $price += $embroidery_price;
                }
            }
		} else {
            $price += $embroidery_price;
		}
	}

	function add_point(&$user, $point)
	{
		global $sql;

        $time = time();
		$pay_table = 'pay';

		if (empty($order)) {
			$pay_where = $sql->setWhere($pay_table, null, "delivery_state", '=', 1);
			$pay_where = $sql->setWhere($pay_table, $pay_where, "pay_state", "=", 1);
			$pay_where = $sql->setWhere($pay_table, $pay_where, "name_ruby", "=", 'granted point');
			$pay_where = $sql->setWhere($pay_table, $pay_where, "name", "=", 'granted point');
			$pay_where = $sql->setWhere($pay_table, $pay_where, "price", "=", 0);
			$pay_where = $sql->setWhere($pay_table, $pay_where, "pay_num", "=", '-');
			$order = $sql->setOrder($pay_table, null, "regist_unix", "ASC");

			$pay_point = $sql->sql_fetch_assoc($sql->getSelectResult($pay_table, $pay_where, $order, [0, 1]));

			$order = $pay_point['id'];
		}

		$s_rec = SystemUtil::getSystemParam();
		$expiry = date('Y-m-d 00:00:00', $time + 3600 * 24 * ($s_rec['upoint_expiration'] + 1));

		createUpoint($user['id'], $point, $order, UPOINT_STATE['available'], $expiry);

		//update current point
		if ($point_rec = $sql->sql_fetch_assoc($sql->rawQuery(sprintf('SELECT SUM(point) as point FROM upoints WHERE `user` = "%s" AND state = %s', $user['id'], UPOINT_STATE['available'])))) {
            $user['point'] = $point_rec['point'];
		}
	}

	/**
	 * Get StatisticPay2 from time
	 *
	 * @param $user
	 * @param $tmp
	 * @return array
	 */
	function getStatisticPay2($user, $tmp) {
		global $sql;

		$subQueries = ' AND pay IN ( SELECT id FROM pay WHERE delivery_state = 1 and design_store_id is null ) ';
		$table = "pay_item";

		$where = '';
		if($user) $where = " AND user = '".$user."' ";
		if($user) $where .= " AND OWNER = '' ";
		$rawQuery = "SELECT SUM(fee_user) FROM pay_item WHERE date_y = ".$tmp["date_y"]." AND date_m = ".$tmp["date_m"]." AND state = 1 ".$subQueries.$where;
		$tmp["fee_user"] = $sql->sql_fetch_assoc($sql->queryRaw($table, $rawQuery))['SUM(fee_user)'];

		$where = '';
		if($user) $where .= " AND OWNER = '".$user."' ";
		$rawQuery = "SELECT SUM(fee_user) FROM pay_item WHERE date_y = ".$tmp["date_y"]." AND date_m = ".$tmp["date_m"]." AND state = 1 ".$subQueries.$where;
		$tmp["fee_user"] += $sql->sql_fetch_assoc($sql->queryRaw($table, $rawQuery))['SUM(fee_user)'];

		if($user) {
			$querySelect = "SELECT SUM(pay_item.fee_owner) AS fee";
			$queryWhere = "
									FROM pay_item INNER JOIN item ON pay_item.item = item.id
												INNER JOIN item item_parent ON item.owner_item = item_parent.id
												INNER JOIN pay ON pay.id = pay_item.pay and pay.design_store_id is null
													WHERE	pay_item.USER = '{$user}'
												AND pay_item.date_y = {$tmp["date_y"]} 
												AND pay_item.date_m = {$tmp["date_m"]} 
												AND pay_item.state = 1 
												AND pay.delivery_state = 1 
												AND item_parent.2nd_margin_state = 0";
			$result = $sql->rawQuery($querySelect . $queryWhere);
			$val = $sql->sql_fetch_assoc($result);
			$tmp["fee_owner"] = $val['fee'] ? $val['fee'] : 0;
		}

		$table = "pay_option";
		$where = '';
		if($user) $where .= " AND OWNER = '".$user."' ";
		$rawQuery = "SELECT SUM(fee_owner) FROM pay_option WHERE date_y = ".$tmp["date_y"]." AND date_m = ".$tmp["date_m"]." AND state = 1 ".$subQueries.$where;
		$tmp["fee_option"] = $sql->sql_fetch_assoc($sql->queryRaw($table, $rawQuery))['SUM(fee_owner)'];

		return array(
			'fee_user' => $tmp["fee_user"],
			'fee_owner' => $tmp["fee_owner"] ? $tmp["fee_owner"] : 0,
			'fee_option' => $tmp["fee_option"]
		);
	}

	function getDisCount($cart_row_totale, $item_price_total)
	{
        $next = 0;
        $discount = 0;
        $discount_par = 0;
        $next_discount_par = 2;
        $discounts = [
            '0~4' => 0,
            '5~9' => 2,
            '10~14' => 5,
            '15~19' => 7,
            '20~29' => 10,
            '30~39' => 15,
            '40~49' => 20,
            '50~59' => 25,
            '60~69' => 30,
            '70~79' => 35,
            '80~89' => 40,
            '90~99' => 45,
            '100' => 50,
        ];

        foreach ($discounts as $counts => $percent) {
            $count_items = explode('~', $counts);

            if (count($count_items) === 2) {
                $next_discount = current($discounts);

                if ($next_discount == $percent) {
                    $next_discount = next($discounts);
				}

                if ($count_items[0] <= $cart_row_totale && $cart_row_totale <= $count_items[1]) {
                    $discount = floor($item_price_total * $percent / 100);
                    $next = $count_items[1] + 1 - $cart_row_totale;
                    $discount_par = $percent;
                    $next_discount_par = $next_discount;
                    break;
                }
            } else {
                if ($count_items[0] <= $cart_row_totale) {
                    $next = 0;
                    $discount = floor($item_price_total * $percent / 100);
                    $discount_par = $percent;
                    $next_discount_par = $percent;
                    break;
                }
            }
        }

        return [$next, $discount, $discount_par, $next_discount_par];
	}

function checkPayStatus($order_id, $pay_type, $rec)
{
    $is_paid = false;

    if (in_array($pay_type, ['cod', 'card', 'rakuten', 'apple_pay', 'pay']) || $rec["pay_type"] == "bank" && $rec["pay_total"] <= 0) {
        $is_paid = true;
    } elseif (in_array($pay_type, ['sb', 'docomo', 'au', 'linepay'])) {
        $pay_types = [
            'au'      => 8,
            'sb'      => 11,
            'docomo'  => 9,
            'linepay' => 20,
        ];

        $result = gmoFunc::checkMulti($order_id, $pay_types[$pay_type]);

        if (!empty($result) && $result['JobCd'] == 'SALES') {
            $is_paid = true;
        }
    }

    return $is_paid;
}

	function add_reward(&$user, $reward)
	{
		global $sql;

		$pay_table = 'pay';
		$time = time();
		$pay_id = SystemUtil::getUniqId($pay_table, false, true);
		$pay_num = getPayNum();

		//create data table pay
		$pay = $sql->setData($pay_table, null, "id", $pay_id);
		$pay = $sql->setData($pay_table, $pay, "pay_num", $pay_num);
		$pay = $sql->setData($pay_table, $pay, "pay_type", "reward_market");
		$pay = $sql->setData($pay_table, $pay, "user", 'admin');
		$pay = $sql->setData($pay_table, $pay, "ipaddress", $_SERVER['REMOTE_ADDR']);
		$pay = $sql->setData($pay_table, $pay, "useragent", $_SERVER['HTTP_USER_AGENT']);
		$pay = $sql->setData($pay_table, $pay, "conf_datetime", date("Y-m-d H:i:s", $time));
		$pay = $sql->setData($pay_table, $pay, "send_datetime", date("Y-m-d H:i:s", $time));
		$pay = $sql->setData($pay_table, $pay, "date_y", date("Y", $time));
		$pay = $sql->setData($pay_table, $pay, "date_m", date("n", $time));
		$pay = $sql->setData($pay_table, $pay, "date_d", date("j", $time));
		$pay = $sql->setData($pay_table, $pay, "regist_unix", $time);
		$pay = $sql->setData($pay_table, $pay, "order_from_app", "UP-T");
		$pay = $sql->setData($pay_table, $pay, "remarks", "");
		$data_pay['data_false'] = ['pay_adjustment', 'gift_price', 'gift_pink', 'gift_blue', 'gift_yellow', 'pay_price', 'pay_promotion', 'deferred_payment', 'pay_postage', 'pay_cod', 'pay_discount', 'pay_tax', 'pay_total', 'just_nobori_order', 'pay_type_add'];
		$data_pay['data_true'] = ['pay_state', 'delivery_state', 'policy_check', 'pending'];
		foreach ($data_pay as $key => $val) {
			foreach ($val as $sub_key => $sub_val) {
				if ($key == 'data_false') {
					$value = 0;
				} elseif ($key == 'data_true') {
					$value = 1;
				}
				$pay = $sql->setData($pay_table, $pay, $sub_val, $value);
			}
		}
		$sql->addRecord($pay_table, $pay);

		//find item reward or create item reward
		$item = findOrCreateItemReward($time);

		//create data table pay_item
		$pay_item_table = 'pay_item';
		$pay_item_id = SystemUtil::getUniqId($pay_item_table, false, true);
		$pay_item = $sql->setData($pay_item_table, null, "id", $pay_item_id);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "item", $item);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "owner", '');
		$pay_item = $sql->setData($pay_item_table, $pay_item, "user", $user["id"]);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "buy_user", 'admin');
		$pay_item = $sql->setData($pay_item_table, $pay_item, "pay", $pay_id);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "item_image", '');
		$pay_item = $sql->setData($pay_item_table, $pay_item, "item_name", 'マーケット開設報酬');
		$pay_item = $sql->setData($pay_item_table, $pay_item, "fee_user", $reward);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "date_y", date("Y", $time));
		$pay_item = $sql->setData($pay_item_table, $pay_item, "date_m", date("n", $time));
		$pay_item = $sql->setData($pay_item_table, $pay_item, "date_d", date("j", $time));
		$pay_item = $sql->setData($pay_item_table, $pay_item, "regist_unix", $time);
		$pay_item = $sql->setData($pay_item_table, $pay_item, "pay_item_num", "{$pay_num}-1");
		$pay_item = $sql->setData($pay_item_table, $pay_item, "state", 1);
		$data_pay = ['item_row', 'item_price', 'fee_owner', 'fee_option', 's_price', 't_price', 'is_layers_print_allowed', 'print_by_layers_activated', 'print_by_layers_plate_price', 'print_by_layers_item_price', 'print_by_layers_front_price', 'print_by_layers_back_price', 'print_by_layers_left_price', 'print_by_layers_right_price', 'print_by_layers_total_price', 'item_type', 'item_type_sub', 'item_type_size'];
		foreach ($data_pay as $key => $val) {
			$pay = $sql->setData($pay_table, $pay, $val, 0);
		}
		$sql->addRecord($pay_item_table, $pay_item);
	}

	/**
	 * find item reward or create item reward
	 * @param $time
	 * @return string
	 */
	function findOrCreateItemReward($time)
	{
		global $sql;
		$table = 'item';
		$where = $sql->setWhere($table, null, 'item_type', '=', 'item_reward');
		$where = $sql->setWhere($table, $where, 'user', '=', 'admin');

		if ($rec = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, array(0, 1)))) {
			$item_id = $rec['id'];
		} else {
			$item_id = SystemUtil::getUniqId($table, false, true);
			$rec = $sql->setData($table, null, "id", $item_id);
			$rec = $sql->setData($table, $rec, "user", 'admin');
			$rec = $sql->setData($table, $rec, "name", 'マーケット開設報酬');
			$rec = $sql->setData($table, $rec, "item_text", 'マーケット開設報酬');
			$rec = $sql->setData($table, $rec, "item_type", 'item_reward');
			$rec = $sql->setData($table, $rec, "edit_unix", $time);
			$rec = $sql->setData($table, $rec, "regist_unix", $time);
			$data['data_false'] = ['image_id', 'item_price', 'fee_user', 'fee_option', 'fee_owner', 'price', 'buy_state', 'buy_count_state', 'buy_count_row', 'rank_count', 'premium_flag', 'pickup'];
			$data['data_true'] = ['2nd_owner_state', 'mall_state', '2nd_state', '2nd_margin_state', '2nd_owner_check', 'state'];
			foreach ($data as $key => $val) {
				foreach ($val as $sub_key => $sub_val) {
					if ($key == 'data_false') {
						$value = 0;
					} elseif ($key == 'data_true') {
						$value = 1;
					}
					$rec = $sql->setData($table, $rec, $sub_val, $value);
				}
			}
			$sql->addRecord($table, $rec);
		}
		return $item_id;
	}

	/**
	 * @param $object
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	function checkSizeExists($object, $key, $value) {
			foreach ($object as $row) {
				if ($row->{$key} == $value) {
					return true;
				}
			}
			return false;
	}

	function updateSession()
	{
		global $sql;
		$table = 'users_sessions';
		$current_session = Globals::session('DRAW_TOOL_SESSION');
		$token = uuid();

		if (!empty($current_session)) {
			$where = $sql->setWhere($table, null, 'token', '=', $current_session);
			$rec = $sql->setData($table, null, 'status_login', 0);
			$rec = $sql->setData($table, $rec, 'user_id', Globals::session("LOGIN_ID"));
			$sql->updateRecordWhere($table, $rec, $where);
		}

		$rec = $sql->setData($table, null, 'user_id', Globals::session("LOGIN_ID"));
		$rec = $sql->setData($table, $rec, 'token', $token);
		$rec = $sql->setData($table, $rec, 'created_at', date('y-m-d H:i:s'));
		$rec = $sql->setData($table, $rec, 'updated_at', date('y-m-d H:i:s'));
		$sql->addRecord($table, $rec);

		Globals::setSession('DRAW_TOOL_SESSION', $token);
	}

	function uuid() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}

	/**
	 * @param $item
	 * @param $item_type
	 * @param $item_sub
	 * @param $item_size
	 * @param $item_price
	 * @param $item_row
	 * @param $preview_image
	 * @param $list_item_sub
	 */
	function appliMultipleItemBlank($item, $item_type, $item_sub, $item_size, $item_price, $item_row,$preview_image,$list_item_sub)
	{
		$cart_name = 'CART_ITEM';
		$cart_data = ['has_embroidery' => 0, 'product_type' => TYPE_PRINT];

		$cart = Globals::session($cart_name);

		$cart_price = $item_price + $item["fee_user"] + $item["fee_owner"];

		if (!empty($item_row)) {
			$cart_row = $item_row;
		} else {
			$cart_row = 1;
		}

		if (is_update_plain_cart($item_sub, $list_item_sub, $item_size, $item_price, $cart)) {
			foreach ($cart as $key => $item) {
				foreach ($item['item_type_size_detail'] as $itemTypeSizeDetail) {
					if (!array_key_exists($item_size, $itemTypeSizeDetail) && $item['item_price'] == $item_price) {
						$cart[$key]['item_type_size_detail'][$item_size] = [
							'item_type_size' => $item_size,
							'total' => $cart_row,
							'product_type' => 'blank'
						];
						Globals::setSession($cart_name, $cart);
					}
				}
			}
		} else {

			$cart_data = array_merge($cart_data, array(
				'design_type' => 'select',
				'cart_id' => SystemUtil::getUniqId("cart", false, true),
				'cart_price' => $cart_price,
				'item_id' => $item["id"],
				'item_type' => $item_type,
				'item_type_sub' => $item_sub,
				'item_type_size_detail' => [
					$item_size => [
						'item_type_size' => $item_size,
						'total' => $cart_row,
					],
				],
				'item_type_size' => $item_size,
				'item_price' => $item_price,
				'image_id' => '',
				'image_preview1' => $preview_image,
				'image_preview2' => '',
				'image_preview3' => '',
				'image_preview4' => '',
				'image_path1' => $preview_image,
				'image_path2' => '',
				'image_path3' => '',
				'image_path4' => '',
				'option_data' => [
					'option_id' => '',
					'option_owner' => '',
					'option_price' => 0
				],
				'option_price' => 0,
				'design_editable' => (($item['owner_item'] == '') && ($item['2nd_state'] == 1) &&
					($item['cart_state'] == 0)) ? 1 : 0,
				'print_by_layers_data' => '',
				'print_by_layers_activated' => 0,
				'design_from' => 'UP-T',
				'card_thank' => 0
			));

			$cart_data['product_type'] = "bl";
			$cart_data['blank_item'] = 1;
			$cart_data['item_type_size_detail'][$item_size]['product_type'] = 'blank';

			//カート追加
			$cart = Globals::session($cart_name);
			$cart[$cart_data["cart_id"]] = $cart_data;

			//Update cart item price
			$cart = Process::updatePrintPriceCart($cart);

			Globals::setSession($cart_name, $cart);
		}

		//カート更新
		refreshCart();

	}

    function is_update_plain_cart($item_sub, $list_item_sub, $item_size, $item_price, $cart)
    {
        $is_update = false;

        if (!empty($list_item_sub) && array_key_exists($item_sub, $list_item_sub)) {
            foreach ($cart as $key => $item) {
                foreach ($item['item_type_size_detail'] as $itemTypeSizeDetail) {
                    if (!array_key_exists($item_size, $itemTypeSizeDetail) && $item['item_price'] == $item_price) {
                        $is_update = true;
                        break;
                    }
                }
            }
        }

        return $is_update;
    }

	function getSideCompre($itemtype) {
        global $sql;
        $site='';
		$query = 'select * FROM master_item_type_sub_sides INNER JOIN master_item_type_sub ON master_item_type_sub_sides.color_id = master_item_type_sub.id Where master_item_type_sub.item_type="'.$itemtype.'"
AND master_item_type_sub.is_main =1';
        $result = $sql->rawQuery($query);
        $i=0;
        while ($rec = $sql->sql_fetch_assoc($result)) {
        	if($i==0){
                $site.=$rec["title"];
			}
			else{
                $site.=",".$rec["title"];
			}
			$i++;

        }
        return $site;
	}

	function calculateTotalPriceIgnoreDiscount()
	{
        $no_discount = getDiscountItem();
		$total_price_discount = 0;
		$total_discount = 0;
		$total_price_no_discount = 0;
		$total_no_discount = 0;

		$items = Globals::session("ITEM_PRICE");
		foreach ($items as $key => $item) {
			if (!in_array($key, $no_discount)) {
				$item_price_discount = $item['price'];
				if (!empty($item['side'])) {
					foreach ($item['side'] as $side => $price) {
						$item_price_discount += $price;
					}
				}
				$total_price_discount += $item_price_discount * $item['total'];
				$total_discount += $item['total'];
			} else {
				$item_price_no_discount = $item['price'];
				if (!empty($item['side'])) {
					foreach ($item['side'] as $side => $price) {
						$item_price_no_discount += $price;
					}
				}
				$total_price_no_discount += $item_price_no_discount * $item['total'];
				$total_no_discount += $item['total'];
			}
		}

		$price_report = discountByTotal($total_price_discount, $total_discount);

		$price_report['price_total'] = $price_report['price_total'] + $total_price_no_discount;
		$price_report['price_discount'] = $price_report['price_discount'] + $total_price_no_discount;
		$price_report['total'] = $price_report['total'] + $total_no_discount;
		Globals::setSession('PRICE_REPORT', $price_report);
		return $price_report;
	}

	function searchCreator($sql, $table, &$where, $operator = 'NOT IN', $force = false)
	{
		if (Globals::get('is_battle') == 1 && Globals::get('is_creator') != 1 || $force) {
			$where = $sql->setWhere($table, $where, 'user', $operator, sprintf('"%s"', implode('","', CREATOR_ID)));
			$where = $sql->setWhere($table, $where, 'battle_time', '>', 1000);
		}
	}

function updateBattleStatus($id, $state)
{
	global $sql;
	$table = Globals::get('type');

	$update = $sql->setData($table, null, 'status', $state);
	$update = $sql->setData($table, $update, 'updated_at', date('Y-m-d H:i:s'));
	$sql->updateRecord($table, $update, $id);
}

function getBattleRank($battle_rank, $position, $ranks, $is_top = false)
{
    $rank = '<tr>
                 <td>
                     %s
                 </td>
                 <td>
                     <a href="/info.php?type=user&id=%s">%s</a>
                     %s
                 </td>
                 <td>%s</td>
             </tr>';
    $lazy_load = 'src="data:image/svg+xml,%3Csvg%20xmlns=%22http://www.w3.org/2000/svg%22%20viewBox=%220%200%20210%20140%22%3E%3C/svg%3E"';

    if ($is_top) {
        $rank_position = sprintf('<div class="image">
                               <img class="lazyload" %s data-src="/common/design/user/css/img/ranking-%s.png" alt="%s-rank">
                               <span>%s</span>
                           </div>', $lazy_load, $ranks[$position], $ranks[$position], $position);
    } else {
        $rank_position = sprintf('%s位', $position);
    }

    $text = '';
    if ($battle_rank['type'] == 1) {
        $detail = sprintf('%s枚', number_format($battle_rank['total_item'], 0, '', ','));
    } else {
        $detail = sprintf('<div class="d-flex-ct align-items-center-ct jus-content-flex-end">
                                    <a href="/info.php?type=item&id=%s"><div class="image"><img class="lazyload" %s data-src="%s" alt="%s"></div></a>
                                </div>', $battle_rank['item_id'], $lazy_load, $battle_rank['item_preview'], $battle_rank['item_name']);

        $text = sprintf('<div class="text-align-right total-item-price">
                                        <p>販売枚数：%s枚</p>
                                    </div>', number_format($battle_rank['total_item'], 0, '', ','));
    }

    return sprintf($rank, $rank_position, $battle_rank['user_id'], $battle_rank['user_name'], $text, $detail);
}

function getBattleMessage($battle_message)
{
    return sprintf('<div class="column-battle">
                    <div class="item bg-ecf9fd">
                        <div class="text-align-left d-flex-ct align-items-center-ct">
                            <div class="d-inline-block avatar-user">to</div>
                            <div class="d-inline-block name-user">%s</div>
                            <div class="d-inline-block time-user">%s</div>
                        </div>
                        <div class="content text-align-left">
                            %s
                        </div>
                    </div>
                </div>', $battle_message['to'], DateTime::createFromFormat('Y-m-d H:i:s', $battle_message['created_at'])->format('Y年m月d日'), $battle_message['message']);
}

function isBattleUser()
{
    global  $sql;

    Globals::setSession('IS_BATTLE_USER', false);

    if (!empty(Globals::session('LOGIN_ID'))) {
        $table = 'item';
        $where = $sql->setWhere($table, null, 'battle_time', '>', 0);
        $where = $sql->setWhere($table, $where, 'user', '=', Globals::session('LOGIN_ID'));

        if ($sql->getRow($table, $where) > 0) {
            Globals::setSession('IS_BATTLE_USER', true);
        }
    }
}

function adminRole()
{
    if (Globals::session("LOGIN_TYPE") == 'admin') {
        if (Globals::session('LOGIN_KEY') == 'yoshimotomarui') {
            if (strpos($_SERVER['REQUEST_URI'], '/search.php?type=battle_artist_ranks') === false
                && strpos($_SERVER['REQUEST_URI'], '/search.php?type=battle_artist_rankings') === false
                && strpos($_SERVER['REQUEST_URI'], '/admin.php?logout=true') === false
            ) {
                HttpUtil::location('/search.php?type=battle_artist_rankings');
            }
        } elseif (Globals::session('LOGIN_KEY') == 'hokuriku') {
            if (strpos($_SERVER['REQUEST_URI'], '/search.php?type=item_campaign_sales&user_type=7') === false
                && strpos($_SERVER['REQUEST_URI'], '/admin.php?logout=true') === false
            ) {
                HttpUtil::location('/search.php?type=item_campaign_sales&user_type=7');
            }
        } elseif (Globals::session('LOGIN_KEY') == 'atjammarui') {
            if (strpos($_SERVER['REQUEST_URI'], '/search.php?type=item_campaign_sales&user_type=9') === false
                && strpos($_SERVER['REQUEST_URI'], '/admin.php?logout=true') === false
                && strpos($_SERVER['REQUEST_URI'], '/search.php?type=battle_artist_ranks') === false
                && strpos($_SERVER['REQUEST_URI'], '/proc.php?run=downloadCsvSaleCampaign') === false
            ) {
                HttpUtil::location('/search.php?type=item_campaign_sales&user_type=9');
            }
        }
    }
}
function fakeStock($original_id = ORIGINAL_ID, $item_sizes = '', $id = 'IT367'){
    global $sql;

    if (empty($item_sizes)) {
        $item_sizes = sprintf('"%s"', implode('","', ORIGINAL_SIZES));
    }

    $item = $sql->selectRecord('master_item_type', $original_id);
    $product = $sql->selectRecord('master_item_type', $id);

    if (!empty($item) && !empty($product)) {
        addStocks('item_stock', $original_id,'item', 'item_type_size_code', $item['item_code'], $product['item_code'], $item_sizes);
        addStocks('blank_item_stock', $original_id,'item_code', 'item_size_code', $item['item_code'], $product['item_code'], $item_sizes);
    }
}

function addStocks($table, $original_id, $item_code_field, $size_code_field, $stock_code, $product_code, $item_sizes)
{
    global $sql;

    $stocks = $sql->rawQuery(sprintf('SELECT * FROM %s WHERE %s = "%s" AND %s IN (SELECT vendor_size_code FROM master_item_type_size WHERE item_type= "%s" AND id IN (%s))', $table, $item_code_field, $stock_code, $size_code_field, $original_id, $item_sizes));

    foreach ($stocks as $stock) {
        addStock($table, $stock, $product_code);
    }
}

function addStock($table, $stock, $item_code)
{
    global $sql;

    $data = null;

    foreach ($stock as $field => $value) {
        if ($field == 'id') {
            $value = SystemUtil::getUniqId($table, false, true);
        }

        if (in_array($field, ['item_code', 'item'])) {
            $value = $item_code;
        }

        $data = $sql->setData($table, $data, $field, $value);
    }

    $sql->addRecord($table, $data);
}

function redirect_item_page($id)
{
    if (!empty($id)) {
        $url = ccDraw::itemCategories([2 => $id], []);

        if (strtolower($url) !== strtolower(sprintf('/item-detail/%s', $id))) {
            header(sprintf('Location: %s', $url), TRUE, 301);
            exit();
        }
    }
}

function generatePackage($package_id)
{
    ini_set('memory_limit','200M');
    global $sql;
    $list = array();
    $where = $sql->setWhere('pay',null,'order_package_id','=',$package_id);
    $order = $sql->setOrder('pay', null, 'pay_num', 'ASC');
    $list_pay = $sql->getSelectResult('pay',$where, $order);
    while ($result = $sql->sql_fetch_assoc($list_pay)) {
        $list[] = $result['id'];
    }

    if(empty($list)) return 'no pay record';

    $package_rec = $sql->selectRecord('order_packages', $package_id);
    $factory_rec = $sql->selectRecord('factories', $package_rec['factory_id']);
    $factory_type = $factory_rec['factory_type'];
    $is_plain = $factory_type == 'plain';
    $delivery_service = $factory_rec['delivery_service'];

    $zip = new ZipArchive();
    $dir = 'pict_archives_tmp';
    FileUtil::mkdirAndClearFile($dir);
    $file_name = $package_id.'.zip';
    $zipfile = $dir .'/'.$file_name;
    $res = $zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($res === true) {
        // 発送用CSVファイル及び納品書の作成
        generateDeliveryCsv($package_id, $list, $delivery_service, $dir, $zip);

        // 印刷用画像及び指示書の作成
        if (!$is_plain) {
            generatePrintImages($list, $package_id, $factory_type, $dir, $zip);
        }
    }
    $zip->close();
    return $zipfile;
}

function generateDeliveryCsv($package_id, $pay_ids, $delivery_service, $output_dir, $zip) {
    global $sql;
    $csv_fd = fopen('php://temp', 'w');
    $csv_headers = array('出荷予定日', 'ｵ客様管理番号', '送ﾘ状種類', 'ｸｰﾙ区分', 'ｵ届ｹ先ｺｰﾄﾞ','ｵ届ｹ先電話番号', 'ｵ届ｹ先電話番号枝番','ｵ届ｹ先名', 'ｵ届ｹ先郵便番号', 'ｵ届ｹ先住所', 'ｵ届ｹ先建物名（ｱﾊﾟｰﾄﾏﾝｼｮﾝ名）', 'ｵ届ｹ先会社･部門名１', 'ｵ届ｹ先会社･部門名２', 'ｵ届ｹ先名略称ｶﾅ', '敬称', 'ｺﾞ依頼主ｺｰﾄﾞ', 'ｺﾞ依頼主電話番号', 'ｺﾞ依頼主電話番号枝番', 'ｺﾞ依頼主名', 'ｺﾞ依頼主郵便番号', 'ｺﾞ依頼主住所', 'ｺﾞ依頼主建物名（ｱﾊﾟｰﾄﾏﾝｼｮﾝ名）', 'ｺﾞ依頼主名略称ｶﾅ', '品名ｺｰﾄﾞ１', '品名１', '品名ｺｰﾄﾞ２', '品名２', '荷扱ｲ１', '荷扱ｲ２','記事', 'ｵ届ｹ予定（指定）日', '配達時間帯区分', 'ｺﾚｸﾄ代金引換額（税込）', 'ｺﾚｸﾄ内消費税額等', '営業所止置ｷ', '営業所ｺｰﾄﾞ', '個数口枠ﾉ印字', '発行枚数', 'ｺﾞ請求先顧客ｺｰﾄﾞ', 'ｺﾞ請求先分類ｺｰﾄﾞ', '運賃管理番号', 'ｵ届ｹ予定ｅﾒｰﾙ利用区分', 'ｵ届ｹ予定ｅﾒｰﾙｱﾄﾞﾚｽ', '入力機種', 'ｵ届ｹ予定ｅﾒｰﾙﾒｯｾｰｼﾞ', 'ｵ届ｹ完了ｅﾒｰﾙ利用区分', 'ｵ届ｹ完了ｅﾒｰﾙｱﾄﾞﾚｽ', 'ｵ届ｹ完了ｅﾒｰﾙﾒｯｾｰｼﾞ', '複数口ｸｸﾘｷｰ', '検索ｷｰﾀｲﾄﾙ１', '検索ｷｰ１', '検索ｷｰﾀｲﾄﾙ２', '検索ｷｰ２', '検索ｷｰﾀｲﾄﾙ３', '検索ｷｰ３', '検索ｷｰﾀｲﾄﾙ４', '検索ｷｰ４', '投函予定ﾒｰﾙ利用区分', '投函予定ﾒｰﾙe-mailｱﾄﾞﾚｽ', '投函予定ﾒｰﾙﾒｯｾｰｼﾞ', '投函完了ﾒｰﾙ（ｵ届ｹ先宛）利用区分', '投函完了ﾒｰﾙ（ｵ届ｹ先宛）e-mailｱﾄﾞﾚｽ', '投函完了ﾒｰﾙ（ｵ届ｹ先宛）ﾒｰﾙﾒｯｾｰｼﾞ', '投函完了ﾒｰﾙ（ｺﾞ依頼主宛）利用区分', '投函完了ﾒｰﾙ（ｺﾞ依頼主宛）ﾒｰﾙﾒｯｾｰｼﾞ', '連携管理番号', '通知ﾒｰﾙｱﾄﾞﾚｽ');
    fputcsv($csv_fd, $csv_headers);

    foreach($pay_ids as $key => $pay)
    {
        $pay_rec = $sql->selectRecord('pay',$pay);
        $billing_tel = empty($pay_rec['store_shop_name']) ? '0120-86-4321' : $pay_rec['store_phone_number'];
        $billing_company = '株式会社カプセルボックス';
        $billing_add_num = empty($pay_rec['store_shop_name']) ? '222-0037' : $pay_rec['store_add_num'];
        $billing_address1 = empty($pay_rec['store_shop_name']) ? '神奈川県横浜市港北区大倉山3-16-19' : $pay_rec['store_add_pre'].$pay_rec['store_add_sub'];
        $billing_address2 = empty($pay_rec['store_shop_name']) ? 'イマス北品川ビル 5F-D' : $pay_rec['store_add_sub2'];
        $pay_total = empty($pay_rec['pay_total']) ? 0 : $pay_rec['pay_total'];
        $pay_num = empty($pay_rec['pay_num']) ? '0767761337' : $pay_rec['pay_num'];
        $ticket_no = empty($pay_rec['ticket_no']) ? '' : $pay_rec['ticket_no'];
        $product_name_1 = '箱類　オリジナルアイテム';
        $delivery_date = getPayDeliveryDate($pay);
        $value_b = ($pay_rec['pay_type'] == 'cod') ? 2 : 7;

        //$csv_row = array('', $pay_rec['pay_num'], '', '', '',$pay_rec['tel'], $pay_rec['name'], $pay_rec['add_num'], $pay_rec['add_pre'].$pay_rec['add_sub'], $pay_rec['add_sub2'], $pay_rec['company'], '', '', '', '', $billing_tel, '', $billing_company, $billing_add_num, $billing_address1, $billing_address2, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '','', '', '', '', $pay_rec['mail'],'', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        $csv_row = array($delivery_date, $value_b, '', '', $ticket_no ,$pay_rec['tel'], '', $pay_rec['name'], $pay_rec['add_num'], $pay_rec['add_pre'].$pay_rec['add_sub'], $pay_rec['add_sub2'], $pay_rec['company'], '', '', '', '', $billing_tel, '', $billing_company, $billing_add_num, $billing_address1, $billing_address2, '', '', $product_name_1, '', '', '', '', '', '', '', $pay_total, '', '', '', '', '',$pay_num, '1', '', '', $pay_rec['mail'],'', '', '', '', '', '', $pay_rec['pay_num'], $pay_rec['pay_num'], '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
        fputcsv($csv_fd, $csv_row);

        $pdf_file_name =  $pay_rec['pay_num'];
        $pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
        $pdf->setOption('encoding', 'utf-8');
        $pdf->generateFromHtml(getPdfContent($pay,'pay'), $output_dir.'/'.$pdf_file_name.'.pdf');
        $zip->addFile($output_dir.'/'.$pdf_file_name.'.pdf', $pdf_file_name.'_納品書.pdf');
    }

    // 伝票番号発行用のCSVファイル作成
    rewind($csv_fd);
    $zip->addFromString('発送情報_'. $package_id .'.csv', mb_convert_encoding(stream_get_contents($csv_fd), "SHIFT_JIS", "UTF-8"));
    fclose($csv_fd);
}

function generatePrintImages($pay_ids, $package_id, $factory_type, $output_dir, $zip) {
    global $sql;
    global $cc;
    $count_pay_item = 0;
    $generate_csv = $factory_type == 'proxy';
    if ($generate_csv) {
        $csv_fd = fopen('php://temp', 'w');
        $csv_headers = array('オリラボ注文番号', '明細番号', '商品名', '商品カラー', '数量', 'デザインURL', 'デザインイメージ', '発注商品URL', '発送元情報', '発送元名義', '発送元住所', '発送元電話番号', '配送先名義', '配送先住所', '配送先電話番号', '納品書有無');
        fputcsv($csv_fd, $csv_headers);
    } else {
        $template = FileUtil::getFile('template/pc/html/parts/factory/pay_item/pdf_pay_item.html');
    }
    $tmp = '';
    foreach($pay_ids as $key => $pay)
    {
        $pay_rec = $sql->selectRecord('pay',$pay);
        $num_item = 0;
        $where = $sql->setWhere('pay_item',null,'pay','=',$pay);
        $order = $sql->setOrder('pay_item', null, "pay_item_num", "ASC");
        $list_pay_item = $sql->getSelectResult('pay_item',$where, $order);
        while ($pay_item = $sql->sql_fetch_assoc($list_pay_item)) {
            $list_preview = array();
            $item = $sql->selectRecord('item',$pay_item['item']);
            $curlChannel = curl_init();
            curl_setopt_array($curlChannel, array(
                CURLOPT_URL => ApiConfig::HOST . '/designs/design/images/print?download=true&factory=true&id=' . $item['image_id'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => array(
                    'Content-type: application/json',
                ),
            ));
            $response = curl_exec($curlChannel);
            curl_close($curlChannel);

            $list_image = json_decode($response, true);
            foreach ($list_image as $i => $url) {
                if (!empty($url) && $i != 'logo') {
                    // Does not generate an error, even 404
                    $context = stream_context_create(array(
                        'http' => array('ignore_errors' => true)
                    ));
                    $fext = ".png";
                    $side_idx = $i;
                    $side_idx = str_replace([1, 2, 3, 4], ['表', '裏', '左', '右'], $side_idx);
                    $design_image_urls[$side_idx] = $url;
                    $f_name = $output_dir . "/" . $pay_item['pay_item_num'] . "_" . $side_idx . $fext;
                    if(!$handle = @fopen($url, 'r')) {
                        $data = $url;
                        $fext = ".txt";
                    } else {
                        $item_preview = file_get_contents($item['item_preview'.$i],false,$context);
                        if(!empty($item["manual_print_image_{$i}"])) {
                            $data = file_get_contents($item["manual_print_image_{$i}"], false, $context);
                            $item_preview = $data;
                        } else {
                            $data = file_get_contents($url, false, $context);
                        }

                        //base 64 image preview
                        $list_preview[$i] = 'data:image/' . $fext . ';base64,' . base64_encode($item_preview);
                    }
                    file_put_contents($f_name, $data);
                    $zip->addFile($f_name,$pay_item['pay_item_num'] . "_" . $side_idx . $fext);
                }
            }

            foreach ($list_preview as $num => $path) {
                $pay_item['item_preview'.$num] = $path;
            }

            $pay_item['company'] = $pay_rec['company'];
            $pay_item['name'] = $pay_rec['name'];
            $pay_item['add_num'] = $pay_rec['add_num'];
            $pay_item['add_text'] = $pay_rec['add_text'];
            $pay_item['pay_num'] = $pay_rec['pay_num'];
            $pay_item['tel'] = $pay_rec['tel'];
            $pay_item['store_add_num'] = $pay_rec['store_add_num'];
            $pay_item['store_add_pre'] = $pay_rec['store_add_pre'];
            $pay_item['store_add_sub'] = $pay_rec['store_add_sub'];
            $pay_item['store_add_sub2'] = $pay_rec['store_add_sub2'];
            $pay_item['store_shop_name'] = $pay_rec['store_shop_name'];
            $pay_item['store_represent_name'] = $pay_rec['store_represent_name'];
            $pay_item['store_phone_number'] = $pay_rec['store_phone_number'];
            $pay_item['send_delivery_slip'] = $pay_rec['send_delivery_slip'];

            // 代理注文の場合は発注管理用のCSVファイルを作成する
            if ($generate_csv) {
                $master_item = $sql->selectRecord('master_item_type',$pay_item['item_type']);
                $master_color = $sql->selectRecord('master_item_type_sub',$pay_item['item_type_sub']);
                $proxy_site_url = $master_item['proxy_site_url'];
                $use_store_address = !empty($pay_item['store_shop_name']);
                $store_address = '〒' . $pay_item['store_add_num'] . $pay_item['store_add_pre'] . $pay_item['store_add_sub'] . $pay_item['store_add_sub2'];
                $dst_name = empty($pay_item['company']) ? $pay_item['name'] : $pay_item['company'] . ' ' . $pay_item['name'];
                $dst_address = '〒' . $pay_item['add_num'] . $pay_item['add_text'];
                $csv_row = array($pay_item['pay_num'], $pay_item['pay_item_num'], $master_item['name'], $master_color['name'], $pay_item['item_row'], $list_image['1'], $item['item_preview1'], $proxy_site_url, $use_store_address ? '発送元を変更する' : '発送元を変更しない', $pay_item['store_shop_name'], $store_address, $pay_item['store_phone_number'], $dst_name, $dst_address, $pay_item['tel'], $pay_item['send_delivery_slip'] == 1 ? '納品書あり' : '不要');
                fputcsv($csv_fd, $csv_row);
            } else {
                $tmp .= $cc->run($template,$pay_item);
            }
            $count_pay_item++;
            $num_item += $pay_item['item_row'];
        }
    }

    if ($generate_csv) {
        rewind($csv_fd);
        $zip->addFromString('注文情報詳細_' . $package_id . '.csv', mb_convert_encoding(stream_get_contents($csv_fd), "SHIFT_JIS", "UTF-8"));
        fclose($csv_fd);
    } else {
        $pdf_file_name = $package_id.'.pdf';
        $pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
        $pdf->setOption('footer-right', '[page]/'.$count_pay_item);
        $pdf->setOption('encoding', 'utf-8');
        $pdf->generateFromHtml($tmp, $output_dir.'/'.$pdf_file_name);
        $zip->addFile($output_dir.'/'.$pdf_file_name, $pdf_file_name);
    }
}

function getPdfContent($list, $type = null)
{
    // FIXME depends environment
    $baseurl = "https://up-t.jp/";
    $pdf = new Pdf('/usr/local/bin/wkhtmltopdf');
    if (PHP_OS == "WINNT")
    {
        $baseurl = "http://localhost/uptjp/";
        $pdf = new Pdf('C:/xampp/wkhtmltopdf/bin/wkhtmltopdf.exe');
    }
    $html = false;
    $body = array();
    $isStore = array();
    $sysdate = time();

    $list = explode("/", $list);
    for($i = 0; $i < count($list); $i++)
    {
        $eof = ($i == (count($list) - 1));
        $result = makeReceipts($list[$i], $eof, $html, $sysdate, $type);
        $body[] = $result['temp'];
        $isStore[] = $result['isStore'];
    }
    $smarty = new Smarty();
    $smarty->assign("isPreview", false);
    $smarty->assign("baseurl", $baseurl);
    $smarty->assign("body", implode($body));
    $smarty->assign("isStore", implode($isStore));
    return $content = $smarty->fetch("template/pdf/receipt.tpl");
}

function addChildrenPay($pay_rec,$list_factory, $list_delivery_date) {
    global $sql;
    $update = $sql->setData('pay',null,'is_parent','1');
    $sql->updateRecord('pay',$update,$pay_rec['id']);
    $where = $sql->setWhere('pay_item',null,'pay','=',$pay_rec['id']);
    $order = $sql->setOrder('pay_item',null,'item_type');
    $list_pay_item_old = $sql->getSelectResult('pay_item',$where,$order);
    $count = 0;
    $list_pay_item_children = array();
    while ($pay_item_old = $sql->sql_fetch_assoc($list_pay_item_old)) {
        $item_type = $sql->selectRecord('master_item_type',$pay_item_old['item_type']);
        $pay_item_old['factory_id'] = $item_type['factory_id'] ? $item_type['factory_id'] : '';
        $pay_item_old['delivery_date'] = $item_type['delivery_date'] ? $item_type['delivery_date'] : '';
        $list_pay_item_children[$count] = $pay_item_old;
        $count ++;
    }

    foreach ($list_factory as $factory_id) {
        foreach ($list_delivery_date as $delivery_date) {
            $table = 'pay';
            $children_pay = $pay_rec;
            $children_pay_id = SystemUtil::getUniqId($table, false, true);
            $children_pay = $sql->setData($table,$children_pay,'id',$children_pay_id);
            $children_pay = $sql->setData($table,$children_pay,'pay_num',getPayNum());
            $children_pay = $sql->setData($table,$children_pay,'parent_pay_id',$pay_rec['id']);
            $children_pay = $sql->setData($table,$children_pay,'pay_price',0);
            $children_pay = $sql->setData($table,$children_pay,'pay_postage',0);
            $children_pay = $sql->setData($table,$children_pay,'pay_discount',0);
            $children_pay = $sql->setData($table,$children_pay,'pay_adjustment',0);
            $children_pay = $sql->setData($table,$children_pay,'pay_tax',0);
            $children_pay = $sql->setData($table,$children_pay,'pay_total',0);
            $children_pay = $sql->setData($table,$children_pay,'charge_log','');
            $children_pay = $sql->setData($table,$children_pay,'factory_id',$factory_id);

            $has_item = false;
            foreach ($list_pay_item_children as $pay_item_children) {
                $table_pay_item = 'pay_item';
                if($pay_item_children['factory_id'] == $factory_id && $pay_item_children['delivery_date'] == $delivery_date) {
                    $pay_item_children = $sql->setData($table_pay_item,$pay_item_children,'parent_pay_item_id',$pay_item_children['id']);
                    $pay_item_children = $sql->setData($table_pay_item,$pay_item_children,'id',SystemUtil::getUniqId($table_pay_item, false, true));
                    $pay_item_children = $sql->setData($table_pay_item,$pay_item_children,'pay',$children_pay['id']);

                    $k = array_search($factory_id, $pay_item_children);
                    unset($pay_item_children[$k]);
                    $sql->addRecord($table_pay_item,$pay_item_children);
                    $has_item = true;
                }
            }

            if ($has_item) {
                $sql->addRecord($table,$children_pay);
            }
        }
    }
}

function syncChildrenPayState($id) {
    global $sql;
    $table = 'pay';
    $pay_rec = $sql->selectRecord($table,$id);
    $where = $sql->setWhere($table,null,'parent_pay_id','=',$id);
    $list_pay_children = $sql->getSelectResult($table,$where);

    while ($pay_children = $sql->sql_fetch_assoc($list_pay_children)) {
        $update_pay = $sql->setData($table,null,'pay_state',$pay_rec['pay_state']);
        $update_pay = $sql->setData($table,$update_pay,'conf_datetime',$pay_rec['conf_datetime']);
        $sql->updateRecord($table,$update_pay,$pay_children['id']);

        $tmp_table = 'pay_item';
        $where_tmp = $sql->setWhere($tmp_table, null, "pay", "=", $pay_children['id']);
        $update_pay_item = $sql->setData($tmp_table, null, "state", $pay_rec['pay_state']);
        $sql->updateRecordWhere($tmp_table, $update_pay_item, $where_tmp);
    }
}

function insert_item_preview($item, $is_create_preview = false)
{
    global $sql;

    $table_imagick = 'master_item_type_imagick';
    $where = $sql->setWhere($table_imagick,null,'item_type','=',$item['item_type']);
    $where = $sql->setWhere($table_imagick,$where,'item_type_sub','=',$item['item_type_sub']);

    if (array_key_exists($item['item_type'], ITEM_PREVIEW) && !empty(ITEM_PREVIEW[$item['item_type']]) || $sql->getRow($table_imagick,$where)) {
        $table = 'item_previews';
        $data = $sql->setData($table, null, 'item_id', $item['id']);
        $preview = $sql->keySelectRecord($table, 'item_id', $item['id']);

        if ($preview && !$is_create_preview) {
            return false;
        }

        if ($is_create_preview) {
            $images = generate_item_previews($item);

            if (!empty($images)) {
                foreach ($images as $key => $image) {
                    $data = $sql->setData($table, $data, $key, $image);
                }
            }
        }

        if ($preview) {
            $sql->updateRecord($table, $data, $preview['id']);
        } else {
            $sql->addRecord($table, $data);
        }
    }
}

function get_item_previews(&$data, $number = 4)
{
    global $sql;

    $image_id = '';

    if (!empty($data['image_id'])) {
        $image_id = $data['image_id'];
    }

    if (!isset($data['item_type'])) {
        $data['item_type'] = '';
    }

    if (!isset($data['item_type_sub'])) {
        $data['item_type_sub'] = '';
    }

    $has_previews = false;
    $preview_name = sprintf('%s_%s_%s_previews', $image_id, $data['item_type'], $data['item_type_sub']);
    $category_name = sprintf('%s_category', $data['item_type']);
    $images = Globals::getItems($preview_name);

    if (!empty($data['item_type']) && in_array($data['item_type'], array_keys(ITEM_PREVIEW)) || $images)
    {
        $has_previews = true;
    } else {
        $imagick_name = sprintf('%s_%s_imagick', $data['item_type'], $data['item_type_sub']);
        $imagick_value = Globals::getItems($imagick_name);

        if ($imagick_value === 1) {
            $has_previews = true;
        } elseif($imagick_value === null) {
            $table_imagick = 'master_item_type_imagick';
            $where = $sql->setWhere($table_imagick,null,'item_type','=',$data['item_type']);
            $where = $sql->setWhere($table_imagick,$where,'item_type_sub','=',$data['item_type_sub']);

            if ($sql->getRow($table_imagick,$where)) {
                $has_previews = true;

                Globals::setItems(1, $imagick_name);
            }
        }
    }

    if ($has_previews) {
        $item_id = '';

        if (empty($images)) {
            if (!empty($data['id'])) {
                $item_id = $data['id'];
            } elseif(!empty($data['item_id'])) {
                $item_id = $data['item_id'];
            } else {
                $item_id = $data['cart_id'];
            }

            if (!empty($item_id)) {
                global $sql;
                $images = $sql->keySelectRecord('item_previews', 'item_id', $item_id);

                if (empty($images['preview1']) && empty($images['preview2'])) {
                    $images = [];
                }
                if (empty($images)) {
                    $data['item_id'] = $item_id;
                    $data['id'] = $item_id;
                    $create_preview = true;
                } else {
                    $create_preview = false;
                }
                insert_item_preview($data, $create_preview);
            }

            $images = $sql->keySelectRecord('item_previews', 'item_id', $item_id);

            Globals::setItems($images, $preview_name);
        }

        if (!empty($images)) {
            $category_id['master_category_style_id'] = Globals::getItems($category_name);

            if (empty($category_id['master_category_style_id'])) {
                $query = sprintf('SELECT master_categories.master_category_style_id
                    FROM master_item_type
					INNER JOIN master_categories ON master_categories.id = master_item_type.category_id
					WHERE  master_item_type.id= "%s"', $data['item_type']);
                $category_id = $sql->sql_fetch_assoc($sql->rawQuery(($query)));

                Globals::setItems($category_id['master_category_style_id'], $category_name);
            }

            if (!empty($data['item_preview1'])) {
                if ($category_id['master_category_style_id'] == PHONECASE_CATEGORY) {
                    if (Globals::get('p')  === 'cart') {
                        $data['item_preview4'] = $data['item_preview1'];
                    } else {
                        $item_preview = $data['item_preview1'];
                        for($i = 1; $i <= 4; $i++) {
                            if (!empty($images["preview$i"])) {
                                $data["item_preview$i"] = $images["preview$i"];
                            } else {
                                $data["item_preview$i"] = $item_preview;
                                break;
                            }
                        }
                    }
                }else{
                    $data['item_preview3'] = $data['item_preview1'];
                }
            } elseif (!empty($data['image_preview1'])) {

                if ($category_id['master_category_style_id'] == PHONECASE_CATEGORY) {
                    if (Globals::get('p')  === 'cart') {
                        $data['image_preview4'] = $data['image_preview1'];
                    } else {
                        $item_preview = $data['item_preview1'];
                        for($i = 1; $i <= 4; $i++) {
                            if (!empty($images["preview$i"])) {
                                $data["item_preview$i"] = $images["preview$i"];
                            } else {
                                $data["item_preview$i"] = $item_preview;
                                break;
                            }
                        }
                    }
                }else{
                    $data['image_preview3'] = $data['image_preview1'];
                }
            }

            for ($i = 1; $i < 5; $i++) {
                $preview = sprintf('preview%s', $i);
                $item_preview = sprintf('item_preview%s', $i);
                $image_preview = sprintf('image_preview%s', $i);

                if (!empty(trim($images[$preview]))) {
                    if (isset($data[$item_preview])) {
                        $data[$item_preview] = $images[$preview];
                    } elseif (isset($data[$image_preview])) {
                        $data[$image_preview] = $images[$preview];
                    }
                }

                if ($i == $number) {
                    break;
                }
            }
        }
    }

}

function generate_item_previews($item, $number = 4)
{
    $images = [];
    $message = '';

    if (empty($item['item_image1']) && !empty($item['image_path1'])) {
        $item['item_image1'] = $item['image_path1'];
    }

    if (empty($item['item_id'])) {
        $item['item_id'] = SystemUtil::getUniqId('', false, true);
    }

    if (!empty($item['item_image1']) && !empty(ITEM_PREVIEW[$item['item_type']])) {
        foreach (ITEM_PREVIEW[$item['item_type']] as $key => $value) {
            if (!empty(COLOR_PREVIEW[$item['item_type']][$item['item_type_sub']][$key]))
            {
                $preview_name = sprintf('%s_%s.png', $item['item_id'], $key + 1);
                $preview_path = sprintf(PREVIEW_PATH, $preview_name);

                shell_exec(sprintf($value, ROOT_PATH, $item['item_image1'], COLOR_PREVIEW[$item['item_type']][$item['item_type_sub']][$key], $preview_path));

                if (file_exists($preview_path)) {
                    $images[sprintf('preview%s', $key + 1)] = SystemUtil::uploadImage(sprintf('PreviewImages/%s_%s.png', $item['item_id'], $key + 1), $preview_path, 'image/png', $message);

                    unlink($preview_path);
                }
            }

            if ($key + 1 == $number) {
                break;
            }
        }
    } else {

        for ($i = 1; $i < 5; $i++) {
            if (!empty($item["item_image$i"]) && empty($item["image_path$i"])) {
                $item["image_path$i"] = $item["item_image$i"];
            }
        }

        $params = '';
        $list_key = ['item_type','item_type_sub','image_path1','image_path2','image_path3','image_path4'];
        foreach ($list_key as $key) {
            if(!empty($value = $item[$key])) {
                $params .= "&$key=$value";
            }
        }
        $url = sprintf("%s/design/product-remake?session=%s%s",ApiConfig::HOST,Globals::session('DRAW_TOOL_SESSION'),$params);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $resultImage = curl_exec($ch);
        curl_close($ch);

        $image_remake = json_decode($resultImage,true);

        for ($i = 0; $i < 4; $i++) {
            if(!empty($image_remake['list_image'][$i])) {
                $num = $i +1;
                $images["preview$num"] = $image_remake['list_image'][$i];
            }
        }
    }

    return $images;
}

function update_filter_type(&$data)
{
    global $sql;

    $dry = 0;
    $act_00300 = 0;
    $cvt_00085 = 0;
    $wundou_value = 0;
    $product_ids = [];
    $has_normal = false;
    $data['filter_able'] = 0;
    $table = 'master_item_type';
    $where = $sql->setWhere($table, null, 'item_code_nominal', 'IN', array_values(FILTER_CODE), 'AND', '(');
    $where = $sql->setWhere($table, $where, 'maker', '=', WUNDOU, 'OR', ")");

    $products = $sql->getSelectResult($table, $where);

    foreach ($products as $product) {
        $nominal = trim($product['item_code_nominal']);

        if (!in_array($nominal, array_keys(FILTER_CODE))) {
            $nominal = WUNDOU;
        }

        $product_ids[$nominal][] = $product['id'];
    }

    foreach (Globals::session('CART_ITEM') as $item) {
        if (is_blank_item($item) || in_array($item['item_type'], NOTO_IDS)) {
            return $data['filter_able'];
        }

        if (in_array($item['item_type'], $product_ids[WUNDOU])) {
            $wundou_value = WUNDOU_VALUE;
        }

        if (in_array($item['item_type'], DRY_IDS)) {
            $dry = DRY_VALUE;
        } elseif (!in_array($item['item_type'], array_merge($product_ids[FILTER_CODE['00085-CVT']], $product_ids[FILTER_CODE['00300-ACT']], $product_ids[WUNDOU]))) {
            $has_normal = true;
        } else {
            if (in_array($item['item_type'], $product_ids[FILTER_CODE['00300-ACT']])) {
                $act_00300 = ACT_00300;
            } else {
                $cvt_00085 = CVT_00085;
            }
        }
    }

    if (!($has_normal && empty($wundou_value))) {
        $data['filter_able'] = $act_00300 + $cvt_00085 + $wundou_value + $dry;
    }
}

function login_from_app($token)
{
    global $sql;

    $table = 'users_sessions';
    $where = $sql->setWhere($table, null, 'token', '=', $token);
    $user = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

    if (!empty($user['user_id'])) {
        Globals::setSession('LOGIN_TYPE', 'user');
        Globals::setSession('LOGIN_ID', $user['user_id']);
    }
}

function is_blank_item($item)
{
    if (!empty($item['product_type']) && $item['product_type'] == 'bl') {
        return true;
    }

    if (!empty($item['item_type_size_detail'])) {
        foreach ($item['item_type_size_detail'] as $size) {
            if(isset($size['product_type']) && $size['product_type'] == 'blank') {
                return true;
            }
        }
    }

    return false;
}

function resize_image($url, $width)
{
    try {
        $imagick = new Imagick;
        $imagick->readImageBlob(file_get_contents(str_replace(['&amp;#039;', '&#039;'], ["'", "'"], $url)));


        $image_width  = $imagick->getImageWidth();
        $image_height = $imagick->getImageHeight();

        if ($width >= $image_width) {
            return $url;
        }

        $imagick->scaleImage($width, $image_height * ($width / $image_width));
        $base64 = base64_encode($imagick->getImageBlob());

        return "data:image/jpeg;base64," . $base64;
    } catch (Exception $exception) {
        return $url;
    }
}

function get_item_preview($item)
{
    $preview = '';

    for ($i = 1; $i <= 4; $i++) {
        $preview = $item[sprintf('item_preview%s', $i)];

        if (!empty($preview)) {
            break;
        }
    }

    return $preview;
}

function get_market_type_items($item_types, $limit, $offset, $market_type = 1, $is_all = false)
{
    global $sql;

    $item_type_condition = '';

    if (!$is_all) {
        $item_type_condition = sprintf(' AND item.item_type IN ("%s")', implode('","', $item_types));
    }

    $query = sprintf('SELECT item.id, item_preview1, item_preview2, item_preview3, item_preview4
                              FROM item JOIN master_item_type MIT ON MIT.id = item.item_type
                              WHERE item.state = 1 AND item.buy_state = 1 AND item.2nd_owner_state = 1 AND item.market_type = "%s"
                              AND item.type_time > 0 AND item.regist_unix > 0%s
                              ORDER BY MIT.item_order DESC, item.type_time DESC, item.regist_unix DESC
                              LIMIT %s OFFSET %s;', $market_type, $item_type_condition, $limit, $offset);

    return $sql->rawQuery($query);
}

function is_market_item($item_id)
{
    global $sql;
    $item = $sql->selectRecord('item', $item_id);

    if (!empty($item)) {
        if ($item['buy_state'] == 1 && $item['2nd_owner_state'] == 1 && $item['state'] == 1) {
            return true;
        }
    }

    return false;
}

function update_cart()
{
    $normal_items   = [];
    $periodic_items = [];
    $cart = Globals::session("CART_ITEM");

    if (!empty(Globals::session('OTHER_CART_ITEM'))) {
        $cart = Globals::session('OTHER_CART_ITEM') + $cart;
        Globals::setSession('OTHER_CART_ITEM', null);
    }

    if (!empty($cart)) {
        foreach ($cart as $key => $val) {
            if ($val['item_id'] != constants::PERIODIC_MASK['item_id']) {
                $normal_items[$key] = $val;
            } else {
                $periodic_items[$key] = $val;
            }
        }
    }

    if (empty($normal_items) && !empty($periodic_items)) {
        Globals::setSession('IS_PERIODIC', true);
    } elseif (empty($periodic_items)) {
        Globals::setSession('IS_PERIODIC', false);
    }

    if (Globals::session('IS_PERIODIC')) {
        Globals::setSession('CART_ITEM', $periodic_items);
        Globals::setSession('OTHER_CART_ITEM', $normal_items);

        if (empty($periodic_items)) {
            set_cart_type(false);
            update_cart_item($normal_items);
        }
    } else {
        update_cart_item($normal_items);
    }

    if (!empty(Globals::session('cart_promotion_code'))) {
        $discount_promotion_code = 0;

        update_promotion_code(Globals::session('cart_promotion_code'), $discount_promotion_code);
    }
}

function update_cart_item($normal_items)
{
    $mask_items  = [];
    $other_items = [];

    foreach ($normal_items as $key => $item) {
        if ($item['item_id'] == constants::MASK['item_id']) {
            $mask_items[$key] = $item;
            unset($normal_items[$key]);
        }
    }

    if (Globals::session('IS_MASK')) {
        if (empty($mask_items)) {
            if (empty($periodic_items)) {
                set_cart_type(false);
            } else {
                Globals::setSession('IS_PERIODIC', true);
            }

            $mask_items = $periodic_items;
        }

        $other_items  = $normal_items;
        $normal_items = $mask_items;
    }

    if (empty($normal_items)) {
        set_cart_type(false);

        $normal_items = $other_items;
    }

    Globals::setSession('CART_ITEM', $normal_items);
    Globals::setSession('OTHER_CART_ITEM', $other_items);
}

function get_mask_prices($quantity)
{
    $total = 0;
    $prices = [];

    if ($quantity > 0) {
        $single = $quantity % 4;
        $group  = ($quantity - $single) / 4;

        if ($single > 0) {
            $total += constants::MASK_PRICE * $single;
            $prices['single'] = [
                'total' => $single,
                'price' => constants::MASK_PRICE,
            ];
        }

        if ($group > 0) {
            $total += constants::MASK_GROUP_PRICE * $group;
            $prices['group'] = [
                'total' => $group,
                'price' => constants::MASK_GROUP_PRICE,
            ];
        }

    }

    return ['total' => $total, 'prices' => $prices];
}

function is_mask($item_id)
{
    return in_array($item_id, [constants::MASK['item_id'], constants::PERIODIC_MASK['item_id']]);
}

function get_mask_quantity($data)
{
    $quantity = 0;

    if (!empty($data['item_id']) && is_mask($data['item_id'])) {
        foreach ($data['item_type_size_detail'] as $size) {
            $quantity += $size['total'];
        }
    }

    return $quantity;
}

function update_cart_session($rec)
{
    if (Globals::session('IS_PERIODIC')) {
        Globals::setSession('IS_PERIODIC_USER', true);
        insert_periodic_pay($rec['id'], $rec['user'], Globals::session('CARD_SEQ'));
    }

    Globals::setSession('IS_MASK', false);
    Globals::setSession('IS_PERIODIC', false);

    if (!empty(Globals::session('OTHER_CART_ITEM'))) {
        Globals::setSession('CART_ITEM', Globals::session('OTHER_CART_ITEM'));

        foreach (Globals::session('OTHER_CART_ITEM') as $cart) {
            if ($cart['item_id'] == constants::PERIODIC_MASK['item_id']) {
                Globals::setSession('IS_MASK', true);
                Globals::setSession('IS_PERIODIC', true);
                break;
            }
        }
    }

    Globals::setSession('CARD_SEQ', '');
    Globals::setSession('OTHER_CART_ITEM', null);
}

function answerSurvey($answers)
{
    global $sql;
    $table = 'survey_answers';
    $rec_temp = $sql->keySelectRecord('user', "id", Globals::session("LOGIN_ID"));
    $answered = $sql->keySelectRecord('survey_answers', "user_id", Globals::session("LOGIN_ID"));
    if($rec_temp && empty($answered)) {
        $point = (int)$rec_temp["point"] +  50;
        $update = $sql->setData('user', null, "point", $point);
        $sql->updateRecord('user', $update, $rec_temp["id"]);
    }
    $today = date('Y-m-d');
    $data = $sql->setData($table, null, 'question_id', 1);
    $data = $sql->setData($table, $data, 'answer', $answers);
    $data = $sql->setData($table, $data, 'user_id', Globals::session("LOGIN_ID"));
    $data = $sql->setData($table, $data, 'regist_unix', strtotime($today));
    $sql->addRecord($table, $data);
}

function get_total_mask_price()
{
    $total = 0;
    $cart  = Globals::session('CART_ITEM');

    foreach ($cart as $item) {
        $total += get_mask_prices(get_mask_quantity($item))['total'];
    }

    return $total;
}

function insert_periodic_pay($pay_id, $user_id, $card_seq)
{
    global $sql;

    $table = 'periodic_orders';
    $data = $sql->setData($table, null, 'order_id', $pay_id);
    $data = $sql->setData($table, $data, 'user_id', $user_id);
    $data = $sql->setData($table, $data, 'card_seq', $card_seq);
    $data = $sql->setData($table, $data, 'created_at', date('Y-m-d'));
    $data = $sql->setData($table, $data, 'last_ordered_at', date('Y-m-d'));

    $sql->addRecord($table, $data);
}

function set_periodic_user()
{
    global $sql;

    Globals::setSession('IS_PERIODIC_USER', false);

    $order = $sql->rawQuery(sprintf('SELECT * FROM periodic_orders WHERE user_id = "%s" AND cancelled_at IS NULL LIMIT 1', Globals::session("LOGIN_ID")));

    if(!empty($sql->sql_fetch_assoc($order))){
        Globals::setSession('IS_PERIODIC_USER', true);
    }
}

function set_cart_type($is_mask = true)
{
    if (!$is_mask) {
        Globals::setSession('IS_MASK', false);
        Globals::setSession('IS_PERIODIC', false);
    } else {
        Globals::setSession('IS_PERIODIC', false);
        Globals::setSession('IS_MASK', false);

        if (in_array(constants::PERIODIC_MASK['item_id'], [Globals::post('item_id'), Globals::get('item_id')])) {
            Globals::setSession('IS_MASK', true);
            Globals::setSession('IS_PERIODIC', true);
        }
    }
}

function mask_delivery_fee()
{
    $delivery_fee = 0;
    $cart = Globals::session("CART_ITEM");

    if (!empty($cart) && is_normal_mask()) {
        $mask_quantity = 0;

        foreach ($cart as $item) {
            $mask_quantity += get_mask_quantity($item);
        }

        if ($mask_quantity) {
            foreach (constants::MASK_QUANTITY_FEE as $quantity => $fee) {
                $quantities = explode('-', $quantity);

                if (count($quantities) === 2) {
                    if ($mask_quantity >= $quantities[0] && $mask_quantity <= $quantities[1]) {
                        $delivery_fee = $fee;

                        break;
                    }
                }
            }
        }
    }

    return $delivery_fee;
}

function is_normal_mask()
{
    if (Globals::session('IS_MASK') && !Globals::session('IS_PERIODIC')) {
        return true;
    }

    return false;
}

function plain_price($cart, $cart_id, $item_type_size)
{
    $price = 0;

    if (!empty($cart[$cart_id]))
    {
        if ($cart[$cart_id]['product_type'] === 'bl') {
            global $sql;

            $query = 'SELECT price FROM master_blank_item_price WHERE item_type = "%s" AND item_type_sub = "%s" AND item_type_size = "%s";';
            $plain_item_price = $sql->sql_fetch_assoc($sql->rawQuery(sprintf($query, $cart[$cart_id]['item_type'], $cart[$cart_id]['item_type_sub'], $item_type_size)));

            if (!empty($plain_item_price['price']) && $cart[$cart_id]['item_price'] != $plain_item_price['price']) {
                $price = $plain_item_price['price'];

                foreach ($cart as $key => $item) {
                    if ($key != $cart_id && $cart[$cart_id]['item_type'] == $cart[$key]['item_type'] &&
                        $price == $cart[$key]['item_price'] &&
                        $cart[$cart_id]['item_type_sub'] == $cart[$key]['item_type_sub']) {
                        $cart_id = $key;
                        break;
                    }
                }
            }
        }
    }

    return ['price' => $price, 'id' => $cart_id];
}

function update_promotion_code($code, &$discount_promotion_code, $no_expired = false)
{
    global $sql;
    $table = 'promotion_code';
    $data  = array();
    $rec   = $sql->keySelectRecord($table, "code", $code);

    if (empty($rec)) {
        $data['msg'] = 'クーポンコードが正しくありません';
    } else {
        $rec['limit1'] = !empty($rec['limit1']) ? $rec['limit1'] : '';
        $rec['limit2'] = !empty($rec['limit2']) ? $rec['limit2'] : '';

        /*check discount type of promotion code*/
        if (!isset($rec['type'])) {
            $item_total = getCartPrice();
            if ($rec['discount_type'] == 'value') {
                $discount_rank           = Extension::discountPrice([2 => 'discount_rank']);
                $discount_promotion_code = min($rec['discount'], $item_total - $discount_rank);
            } else {
                $discount_promotion_code = floor($item_total * $rec['discount']);
            }
        } else {
            $discount_promotion_code = Process::caculatePromotionLimit($rec, $no_expired);
        }
        if ($rec['code_type'] == '0') {
            if ($rec['state'] == 0 || $no_expired && $rec['state'] == 2) {
                Globals::setSession("discount_promotion_code", $discount_promotion_code);
                Globals::setSession("promotion_code_id", $rec['id']);
                $data['msg']      = 'クーポンコードを適用しました';
                $data['discount'] = $discount_promotion_code;
                $data['total']    = getUpPoint();
                $data['point']    = Extension::discountPrice([2 => 'point']);
            } else {
                $data['msg'] = 'クーポンコードは使用済みです';
            }
        } else {
            if ($rec['expire'] >= time()) {
                Globals::setSession("discount_promotion_code", $discount_promotion_code);
                Globals::setSession("promotion_code_id", $rec['id']);
                $data['msg']      = 'クーポンコードを適用しました';
                $data['discount'] = $discount_promotion_code;
                $data['total']    = getUpPoint();
                $data['point']    = Extension::discountPrice([2 => 'point']);
            } else {
                $data['msg'] = 'クーポンコードが期限切れです';
            }
        }
    }

    if (empty($discount_promotion_code)) {
        $code = '';
    }

    Globals::setSession('cart_promotion_code', $code);
    Globals::setSession("discount_promotion_code", $discount_promotion_code);

    return $data;
}


function drawInputAssetTemplate($id, $data = null)
{
    global $sql;
    $tmp = '';

    $rec = $sql->selectRecord('asset_template', $id);
    $inputs = json_decode($rec['input'], true);
    foreach ($inputs as $key => $val) {
        if (empty($data)) {
            $value = '';
        } else {
            $value = $data[$key];
        }

        $input = '';
        if ($val['type'] == 'text' && $val['input'] > 0 && $key != 'alt-text') {
            $input = sprintf('<p>%s</p><input type="text" name="%s[]" value="%s" size="40" maxlength="32">', $key, $key, $value);
        } elseif ($val['type'] == 'textarea' && $val['input'] > 0) {
            $input = sprintf('<p>%s</p><textarea name="%s[]" cols="30" rows="100">%s</textarea>', $key, $key, $value);
        } elseif ($val['type'] == 'number' && $val['input'] > 0) {
            $input = sprintf('<p>%s</p><input type="number" name="%s[]" value="%s" size="40" maxlength="32" min="1">', $key, $key, $value);
        } elseif ($val['type'] == 'file' && $val['input'] > 0) {
            for ($i = 0; $i < $val['input']; $i++) {
                if (is_array($value)) {
                    $image = $value[$i];
                    $alt = $data['alt-text'][$i];
                } else {
                    $image = $value;
                    $alt = $data['alt-text'];
                }
                $input .= sprintf('<p>%s%s</p><input type="file" name="%s[]"><br><p>ALT %s</p><input type="text" name="alt-text[]" value="%s" size="40" maxlength="32"><br>', $key, $i + 1, $key, $i + 1, $alt);
                if (!empty($data)) {
                    $input .= sprintf('<input type="text" name="img-src[]" value="%s" hidden>', $image);
                }
            }
        } elseif ($val['type'] == 'checkbox') {
            if (empty($value)) {
                $input = sprintf('<p>%s</p><input type="checkbox" name="%s[]" value="1">', $key, $key);
            } else {
                $input = sprintf('<p>%s</p><input type="checkbox" name="%s[]" value="1" checked>', $key, $key);
            }
        }

        if (!empty($input)) {
            $tmp .= sprintf('<tr><td class="form">%s</td></tr>', $input);
        }
    }

    return $tmp;
}

function getPayDeliveryDate($pay_id) {
    global $sql;
    $content = '';
    $table = 'pay_item';
    $list_send_date = array();
    $pay_rec = $sql->selectRecord('pay',$pay_id);
    $where = $sql->setWhere($table,null,'pay','=',$pay_id);
    $group = $sql->setGroup($table,null,'item_type');

    $list_pay_item = $sql->getSelectResult($table,$where,null,null,null,$group);
    if($list_pay_item->num_rows) {
        while ($pay_item_rec = $sql->sql_fetch_assoc($list_pay_item)) {
            $item_type_rec = $sql->selectRecord('master_item_type',$pay_item_rec['item_type']);
            $start_date = date("Y-m-d H:i",$pay_rec['regist_unix']);
            if(in_array($pay_item_rec['item_type'],LIST_NOBORI_ITEM_TYPE)) {
                $send_date = calc_senddate($start_date, false, true,false,false,'1');
            } elseif ($item_type_rec['flag_same_day'] == 1) {
                $send_date = calc_senddate($start_date,false, false,true, true,1);
            } elseif (!empty($item_type_rec['delivery_date'])) {
               $send_date =  calc_senddate($start_date,false, false,false, false,1,$item_type_rec['delivery_date']);
            } else {
                $send_date = calc_senddate($start_date,false,false,false,false,'1');
            }

            $list_send_date[] = str_replace(array('年','月','日'),array('/','/',''),$send_date[1]);
        }

        usort($list_send_date, function ($a, $b) {
            return strtotime($a) - strtotime($b);
        });

        $content = end($list_send_date);
    }

    return $content;

}

/**
 * @param $item_type
 * @param $item_type_sub
 * @return array
 */
function getItemPrice($item_type, $item_type_sub)
{
    global $sql;

    if ($rec = $sql->keySelectRecord("master_item_type_option_price", "item_type", $item_type)) {
        $item_price = $rec['original_price'];
    } else {
        $rec = $sql->sql_fetch_assoc($sql->rawQuery("select item_price from master_item_type where id ='$item_type'"));
        $item_price = $rec['item_price'];
    }

    if ($s_rec = $sql->keySelectRecord("master_item_type_option_price_sub", "item_type_sub", $item_type_sub)) {
        $cost1 = $s_rec['cost1_original'];
        $cost2 = $s_rec['cost2_original'];
        $cost3 = $s_rec['cost3_original'];
    } else {
        $s_rec = $sql->sql_fetch_assoc($sql->rawQuery("select cost1, cost2, cost3 from master_item_type_sub where id ='$item_type_sub'"));
        $cost1 = $s_rec['cost1'];
        $cost2 = $s_rec['cost2'];
        $cost3 = $s_rec['cost3'];
    }

    return array(
        'item_price' => $item_price,
        'cost1' => $cost1,
        'cost2' => $cost2,
        'cost3' => $cost3,
        'cost4' => $cost3
    );
}
/**
 * Get tool item price
 *
 * @param $data
 * @param $item_type
 * @param $item_type_sub
 * @return mixed
 */
function getToolItemPrice($data,$item_type, $item_type_sub) {
    global $sql;

    $result = getItemPrice($item_type, $item_type_sub);
    $item_price = $result['item_price'];

    if(empty($data['item_preview1']) && empty($data['item_preview2'])
        && empty($data['item_preview3']) && empty($data['item_preview4']) ) {
        $item = $sql->sql_fetch_assoc($sql->rawQuery(sprintf("SELECT item_preview1, item_preview2, item_preview3, item_preview4 FROM item WHERE id = '%s'",$data['id'])));
        for ($i = 1; $i <= 4; $i++) {
            $data['item_preview' . $i] = $item['item_preview' . $i];
        }
    }

    $embroidery_print = null;
    if (!empty($data['embroidery_print'])) {
        $embroidery_print = json_decode($data['embroidery_print'], true);
    }

    for ($i = 1; $i <= 4; $i++) {
        if (!empty($embroidery_print)) {
            if (!empty($embroidery_print['embroidery'][$i])) {
                if (!empty($embroidery_print['embroidery'][$i])) {
                    getEmbroideryPrice($embroidery_print['embroidery'][$i], $item_price, EMBROIDERY_PRICE[$i]);
                }
            }

            if (!empty($embroidery_print['print'][$i])) {
                $item_price += $result["cost{$i}"];
            }
        } else {
            if (!empty($data["item_preview{$i}"])) {
                $item_price += $result["cost{$i}"];
            }
        }
    }

    return $item_price;
}

function getFreeUserOriginal($price_original, $item)
{
    if (empty($item['fee_user_original'])) {
        return ($item['item_price'] - $price_original) + $item['fee_user'];
    } else {
        return $item['fee_user_original'];
    }
}

function parallel_curl_exec(array $curls, $limit = 10)
{
    $mh = curl_multi_init();
    $queue = []; // キュー
    $errors = array_fill_keys(array_keys($curls), null); // 順番が揃うように最初にキーを埋めておく
    $count = 0; // 現在稼働中のリクエスト数

    // 出来る限りリクエストをプールに追加し，オーバーしたぶんはキューに入れる
    foreach ($curls as $i => $ch) {
        if ($count < $limit) {
            if (CURLM_OK === curl_multi_add_handle($mh, $ch)) {
                ++$count;
            }
        } else {
            $queue[] = $ch;
        }
    }

    // リクエスト実行開始
    curl_multi_exec($mh, $active);

    do {

        // 最大0.5秒間の間監視し，結果をプールに反映する
        curl_multi_select($mh, 0.5);
        curl_multi_exec($mh, $active);

        // 一度すべての結果を取り出す
        // このときにエラーコードの配列を埋めておく
        // (ここでプールから除去もしてしまうとエラーになるので注意！一旦すべて取り出す必要がある)
        $entries = [];
        do if ($entry = curl_multi_info_read($mh, $remains)) {
            $errors[array_search($entry['handle'], $curls)] = $entry['result'];
            $entries[] = $entry;
        } while ($remains);

        // 取り出された数だけリクエストをプールから除去し，キューからまだのぶんを追加する
        foreach ($entries as $entry) {
            curl_multi_remove_handle($mh, $entry['handle']);
            --$count;
            if ($ch = array_shift($queue) and CURLM_OK === curl_multi_add_handle($mh, $ch)) {
                ++$count;
            }
        }

    } while ($count > 0 || $queue); // 稼働中のリクエストとキューが無くなるまでループする

    return $errors;
}


function is_limit_items_by_user($item, $get_category_id = false)
{
    $users = constants::LIMIT_ITEMS_BY_USERS;

    if (!empty($users[$item['user']])) {
        foreach ($users[$item['user']]['categories'] as $category_id => $item_ids) {
            if (in_array($item['item_type'], $item_ids)) {
                if ($get_category_id) {
                    return $category_id;
                }

                return true;
            }
        }
    }

    return false;
}

function getSideNames($title)
{
    $titles     = explode(',', $title);
    $side_names = [];

    foreach ($titles as $title) {
        $side_names[$title] = $title;
    }

    return implode(',', $side_names);
}

function is_detail_page()
{
    if (in_array(Globals::get('type'), ['master_item_type', 'master_item_web_categories', 'master_item_web_sub_categories'])) {
        return true;
    }

    return false;
}

function getQuerySurvey()
{
    $query = 'SELECT s.id, s.question_id, s.user_id,u.add_pre, s.answer, s.regist_unix, u.name AS user_name 
                            FROM survey_answers AS s 
                                INNER JOIN user AS u ON s.user_id = u.id ';
    return $query;

}

function getDiscountItem(){
    global $sql;
    $array = array();

    $where = $sql->setWhere('master_item_type',null,'sales_status', '=', 1);
    $items = $sql->getSelectResult('master_item_type',$where);

    foreach ($items as $item){
        $array[] = $item['id'];
    }

    return $array;
}

function getItemRanking($category){
    global $sql;
    $category_id = $category;
    if (empty($category_id)) $category_id = 2;
    $items = [];
    $table = 'item_ranking';
    $where = $sql->setWhere($table,null,'category_type','=',$category_id);
    $result = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where));

    $table = 'master_item_type';
    $clume = $sql->setClume($table, null, 'id');
    $clume = $sql->setClume($table, $clume, 'name');
    $clume = $sql->setClume($table, $clume, 'tool_price');
    $clume = $sql->setClume($table, $clume, 'name');
    $clume = $sql->setClume('master_item_type_page', $clume, 'preview_image');
    $where = $sql->setWhere($table,null,'id','=',$result['no_1']);
    $inner_join = $sql->setInnerJoin('master_item_type_page', 'master_item_type', 'id', 'master_item_type_page', 'item_type');
    $result1 = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, null, $clume,null,$inner_join));
    $items[1] = $result1;

    $where = $sql->setWhere($table,null,'id','=',$result['no_2']);
    $result2 = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, null, $clume,null,$inner_join));
    $items[2] = $result2;

    $where = $sql->setWhere($table,null,'id','=',$result['no_3']);
    $result3 = $sql->sql_fetch_assoc($sql->getSelectResult($table, $where, null, null, $clume,null,$inner_join));
    $items[3] = $result3;

    return $items;
}

function get_field_data($table, $key_field, $value_field, $parent_field = '', &$other_data = [], $other_field = '', $other_key = '')
{
    global $sql;

    $data          = [];
    $select_fields = "$key_field, $value_field";

    if (!empty($other_data)) {
        $select_fields .= ", state";
    }

    if ($other_field && $other_key) {
        $select_fields .= ", $other_field, $other_key";
    }

    if ($parent_field) {
        $select_fields .= ", $parent_field";
    }

    $items = $sql->rawQuery("SELECT $select_fields FROM $table;");

    foreach ($items as $item) {
        $key = sprintf('%s', ltrim($item[$key_field], '0'));

        if (!empty($other_data)) {
            $other_data['states'][$item['id']] = $item['state'];
            $other_data['item_codes'][$item['id']] = $item[$key_field];
        }

        if ($other_field && $other_key) {
            $data[$other_field][$item[$other_field]] = $item[$other_key];
        }

        if ($parent_field) {
            if ($table === 'master_item_stock_type') {
                $data[$item["$key_field"]][] = $item[$value_field];
                $data["$key"][]              = $item[$value_field];
            } else {
                $data[$item[$parent_field]][$item["$key_field"]] = $item[$value_field];
                $data[$item[$parent_field]]["$key"]              = $item[$value_field];
            }
        } else {
            $data[$item["$key_field"]] = $item[$value_field];
            $data["$key"]              = $item[$value_field];
        }
    }

    return $data;
}

function insert_or_update(&$index, $data, $query, &$item_value, $end = false)
{
    global $sql;

    if ($index === 0 && $data) {
        $item_value = sprintf('("%s")', implode('","', $data));
    } elseif ($end || $index % 1000 === 0) {
        if ($item_value) {
            $sql->rawQuery(sprintf($query, $item_value));
            $item_value = '';
        }

        if (!$end && $data) {
            $item_value = sprintf('("%s")', implode('","', $data));
        }
    } elseif ($data) {
        $item_value .= sprintf(', ("%s")', implode('","', $data));
    }

    $index++;
}

function update_order_by_keyword($table, $order)
{
    global $sql;
    $update = "UPDATE master_keyword SET order_by = CASE id";
    $ids = '';

    $where = $sql->setWhere($table, null, 'order_by', '>=', $order);
    $rec = $sql->getSelectResult($table, $where);
    while($item = $sql->sql_fetch_assoc($rec))
    {
        $update .= sprintf(' WHEN %s THEN %s', $item['id'], $item['order_by'] + 1) ;

        $ids .= $item['id'] . ',';
    }

    $sql->rawQuery($update . ' ELSE order_by END WHERE id IN (' . trim($ids, ",") . ');');
}


function getMasterCategory($limit, $offset) {
    global $sql;
    $tmp = '';

    $table = "master_categories";
    $where = $sql->setWhere($table, null, "is_deleted", "=", 0);
    $where = $sql->setWhere($table, $where, "is_top_page", "=", 1);
    $order = $sql->setOrder($table, null, 'top_page_order', 'ASC');
    $count = $sql->getRow($table, $where);
    $result = $sql->getSelectResult($table, $where, $order, array($offset, $limit));

    while ($rec = $sql->sql_fetch_assoc($result)) {
        $tmp .= '<a href="/item-detail/category/' . $rec['id'] . '" class="d-flex-ct" style="align-items: center">' . '<img src="' . $rec['image_url'] . '" style="width: 40px"><span>' . $rec['title'] . '</span></a>';
    }

    return [$count, $tmp];
}