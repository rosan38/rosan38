<?php

// 例：$PROC_PERMISSION[ユーザー種別][関数名]
    $PROC_PERMISSION['admin']['updatePolicyStatus'] = true;
    $PROC_PERMISSION['admin']['updatePolicyStatusPasral'] = true;
	$PROC_PERMISSION['admin']['godlogin'] = true;
	$PROC_PERMISSION['admin']['delete'] = true;
	$PROC_PERMISSION['admin']['multiDelete'] = true;

	$PROC_PERMISSION['admin']['filedelete'] = true;
	$PROC_PERMISSION['user']['filedelete'] = true;

	$PROC_PERMISSION['user']['returnlogin'] = true;
	$PROC_PERMISSION['agency']['returnlogin'] = true;

	$PROC_PERMISSION['nobody']['passReminder'] = true;
	$PROC_PERMISSION['nobody']['passEdit'] = true;
	$PROC_PERMISSION['user']['passEdit'] = true;

	$PROC_PERMISSION['nobody']['jsonChildList'] = true;
	$PROC_PERMISSION['user']['jsonChildList'] = true;
	$PROC_PERMISSION['admin']['jsonChildList'] = true;

	$PROC_PERMISSION['nobody']['social'] = true;

	$PROC_PERMISSION['agency']['agency2user'] = true;

	$PROC_PERMISSION['nobody']['web2appli'] = true;
	$PROC_PERMISSION['user']['web2appli'] = true;

	$PROC_PERMISSION['nobody']['createBlankItem'] = true;
	$PROC_PERMISSION['user']['createBlankItem'] = true;
	$PROC_PERMISSION['nobody']['web2web'] = true;
	$PROC_PERMISSION['user']['web2web'] = true;
	$PROC_PERMISSION['user']['webBaseOrder'] = true;
	$PROC_PERMISSION['user']['orderAgainformhis'] = true;

	$PROC_PERMISSION['nobody']['appli2web'] = true;
	$PROC_PERMISSION['user']['appli2web'] = true;
	$PROC_PERMISSION['admin']['appli2web'] = true;

	$PROC_PERMISSION['nobody']['appliPasral'] = true;
	$PROC_PERMISSION['user']['appliPasral'] = true;
	$PROC_PERMISSION['admin']['appliPasral'] = true;

	$PROC_PERMISSION['nobody']['web2webDirect'] = true;
	$PROC_PERMISSION['user']['web2webDirect'] = true;

	$PROC_PERMISSION['nobody']['addCart'] = true;
	$PROC_PERMISSION['nobody']['changeCart'] = true;
	$PROC_PERMISSION['nobody']['changeCartPasral'] = true;
	$PROC_PERMISSION['nobody']['addSizeCart'] = true;
	$PROC_PERMISSION['nobody']['delSizeCart'] = true;
	$PROC_PERMISSION['nobody']['changeSpecification'] = true;
	$PROC_PERMISSION['nobody']['delCart'] = true;
	$PROC_PERMISSION['nobody']['copyCart'] = true;
	$PROC_PERMISSION['nobody']['newColor'] = true;
	$PROC_PERMISSION['nobody']['changeCartGift'] = true;

	$PROC_PERMISSION['user']['addCart'] = true;
	$PROC_PERMISSION['user']['changeCart'] = true;
	$PROC_PERMISSION['user']['changeCartPasral'] = true;
	$PROC_PERMISSION['user']['addSizeCart'] = true;
	$PROC_PERMISSION['user']['delSizeCart'] = true;
	$PROC_PERMISSION['user']['changeSpecification'] = true;
	$PROC_PERMISSION['user']['delCart'] = true;
	$PROC_PERMISSION['user']['copyCart'] = true;
	$PROC_PERMISSION['user']['newColor'] = true;
	$PROC_PERMISSION['user']['changeCartGift'] = true;
	$PROC_PERMISSION['user']['changeCartStudent'] = true;
	$PROC_PERMISSION['nobody']['changeCartStudent'] = true;

	$PROC_PERMISSION['user']['changeOwnerState'] = true;
	$PROC_PERMISSION['user']['changeMultiOwnerState'] = true;

	$PROC_PERMISSION['admin']['changeMasterState'] = true;
	$PROC_PERMISSION['admin']['changeMultiMasterState'] = true;

	$PROC_PERMISSION['admin']['changePayDelivery'] = true;
	$PROC_PERMISSION['user']['changePayDelivery'] = true;
	$PROC_PERMISSION['admin']['changePaySend_delivery_slip'] = true;
	$PROC_PERMISSION['admin']['changeMultiPayDelivery'] = true;
	$PROC_PERMISSION['admin']['changeMultiPirnt'] = true;
	$PROC_PERMISSION['admin']['changeMultiGarment'] = true;
	$PROC_PERMISSION['admin']['changePayPay'] = true;
	$PROC_PERMISSION['user']['changePayPay'] = true;
	$PROC_PERMISSION['admin']['exportToPrintty'] = true;
    $PROC_PERMISSION['admin']['changeMultiPolicyCheck'] = true;
    $PROC_PERMISSION['admin']['checkOrderIsBlankItemOnly'] = true;

    $PROC_PERMISSION['admin']['changeStateTemplate'] = true;

	$PROC_PERMISSION['admin']['changeUserFee'] = true;
	$PROC_PERMISSION['admin']['changeMultiUserFee'] = true;

	$PROC_PERMISSION['admin']['printReceipt'] = true;
    $PROC_PERMISSION['admin']['printReceiptPasral'] = true;
	$PROC_PERMISSION['nobody']['printReceipt'] = true;

	$PROC_PERMISSION['nobody']['printPreviewReceipt'] = true;
	$PROC_PERMISSION['user']['printPreviewReceipt'] = true;

	$PROC_PERMISSION['user']['addItemFavorite'] = true;
	$PROC_PERMISSION['user']['delItemFavorite'] = true;

	$PROC_PERMISSION['user']['itemReg'] = true;

	$PROC_PERMISSION['admin']['csvProc'] = true;
	$PROC_PERMISSION['admin']['csvUserInfo'] = true;
    $PROC_PERMISSION['admin']['csvUserInfoPasral'] = true;
	$PROC_PERMISSION['admin']['pictProc'] = true;

	$PROC_PERMISSION['admin']['changeMultiItemState'] = true;
	$PROC_PERMISSION['admin']['changeMultiItemMarketState'] = true;
	$PROC_PERMISSION['admin']['hiddenAllItemOfSelectedUser'] = true;
	$PROC_PERMISSION['admin']['changeMultiItemCartState'] = true;

	$PROC_PERMISSION['admin']['registAfterShip'] = true;
	$PROC_PERMISSION['admin']['registAfterShip2'] = true;

	$PROC_PERMISSION['admin']['changePayPayAfter'] = true;

	$PROC_PERMISSION['admin']['editPayItem'] = true;
    $PROC_PERMISSION['admin']['editPayPasralItem'] = true;
	$PROC_PERMISSION['admin']['changeCartUnit'] = true;
    $PROC_PERMISSION['admin']['changeCartUnitPasral'] = true;

	$PROC_PERMISSION['admin']['import_stock'] = true;
	$PROC_PERMISSION['nobody']['import_stock_markless'] = true;
	$PROC_PERMISSION['admin']['import_stock_markless'] = true;
	$PROC_PERMISSION['admin']['upload_csv_delivery'] = true;
	$PROC_PERMISSION['admin']['import_blank_item_price'] = true;
	$PROC_PERMISSION['admin']['saveMemo'] = true;
	$PROC_PERMISSION['admin']['updateMailStatus'] = true;
    $PROC_PERMISSION['admin']['getUserListSendWillmail'] = true;
    $PROC_PERMISSION['admin']['createWillmailList'] = true;

	$PROC_PERMISSION['admin']['downloadCsvBlankItemStatistic'] = true;

	$PROC_PERMISSION['admin']['changePayGift'] = true;
    $PROC_PERMISSION['admin']['changePayGiftPasral'] = true;
	$PROC_PERMISSION['admin']['changePayAdjustment'] = true;
	$PROC_PERMISSION['admin']['merge2order'] = true;
    $PROC_PERMISSION['admin']['merge2orderPasral'] = true;
	$PROC_PERMISSION['admin']['getUniqueId'] = true;
    $PROC_PERMISSION['admin']['getUniqueIdPasral'] = true;
	$PROC_PERMISSION['admin']['downloadZipCsv'] = true;
    $PROC_PERMISSION['admin']['downloadZipCsvPasral'] = true;
    $PROC_PERMISSION['admin']['updateDeliveryService'] = true;
    $PROC_PERMISSION['admin']['updateTrackingNumber'] = true;

    $PROC_PERMISSION['user']['deleteStore']          = true;
    $PROC_PERMISSION['user']['getStoreById']         = true;
    $PROC_PERMISSION['user']['addStoreInfo']         = true;
    $PROC_PERMISSION['user']['downloadReceipt']      = true;
    $PROC_PERMISSION['admin']['downloadCsvShipment'] = true;

    $PROC_PERMISSION['admin']['changeMasterIsMain'] = true;

    $PROC_PERMISSION['user']['printReceipt'] = true;

    $PROC_PERMISSION['admin']['searchMessage']         = true;

    $PROC_PERMISSION['admin']['handleCategoryAndTag'] = true;

    $PROC_PERMISSION['admin']['updatePendingStatus'] = true;

    $PROC_PERMISSION['admin']['changeNoboriStt'] = true;
    $PROC_PERMISSION['admin']['changeMultiNoboriStt'] = true;

    $PROC_PERMISSION['user']['deleteCreditCard'] = true;

    $PROC_PERMISSION['admin']['changePrinttyExport'] = true;
    $PROC_PERMISSION['user']['getPayItemById'] = true;
    $PROC_PERMISSION['nobody']['getPayItemById'] = true;
    $PROC_PERMISSION['nobody']['loginLine'] = true;
    $PROC_PERMISSION['nobody']['loginInstagram'] = true;
    $PROC_PERMISSION['nobody']['loginInstagramCallback'] = true;
    $PROC_PERMISSION['user']['displayErrorCreditCard'] = true;
    $PROC_PERMISSION['nobody']['displayErrorCreditCard'] = true;
    $PROC_PERMISSION['admin']['addCouponPoint'] = true;
    $PROC_PERMISSION['admin']['getCodeTypeCoupon'] = true;
    $PROC_PERMISSION['user']['getTokenCode'] = true;

    $PROC_PERMISSION['user']['getAddressUser'] = true;

    $PROC_PERMISSION['user']['searchContactShop']         = true;

    $PROC_PERMISSION['user']['checkRefreshToken'] = true;

    $PROC_PERMISSION['user']['updateBaseOrder'] = true;
    $PROC_PERMISSION['admin']['createPromotionCode'] = true;
    $PROC_PERMISSION['user']['addPromotionCode'] = true;
    $PROC_PERMISSION['nobody']['addPromotionCode'] = true;
    $PROC_PERMISSION['user']['removePromotionCode'] = true;
    $PROC_PERMISSION['nobody']['removePromotionCode'] = true;

    $PROC_PERMISSION['admin']['receiveGmoNotifications'] = true;
    $PROC_PERMISSION['user']['receiveGmoNotifications'] = true;
    $PROC_PERMISSION['nobody']['receiveGmoNotifications'] = true;

    $PROC_PERMISSION['user']['upload_img_post'] = true;
    $PROC_PERMISSION['admin']['upload_img_post'] = true;

    $PROC_PERMISSION['admin']['changeItemSameDay'] = true;

    $PROC_PERMISSION['nobody']['sendDateFast'] = true;
    $PROC_PERMISSION['user']['sendDateFast'] = true;

    $PROC_PERMISSION['nobody']['sendDate'] = true;
    $PROC_PERMISSION['user']['sendDate'] = true;


    $PROC_PERMISSION['user']['createCheckoutSessionAmazon'] = true;
    $PROC_PERMISSION['nobody']['createCheckoutSessionAmazon'] = true;

    $PROC_PERMISSION['user']['updateCheckoutSessionAmazon'] = true;
    $PROC_PERMISSION['nobody']['updateCheckoutSessionAmazon'] = true;

    $PROC_PERMISSION['nobody']['saveDataFromLocalStorage'] = true;
    $PROC_PERMISSION['user']['saveDataFromLocalStorage'] = true;

    $PROC_PERMISSION['nobody']['valiateDataAmazonPay'] = true;
    $PROC_PERMISSION['user']['valiateDataAmazonPay'] = true;

    $PROC_PERMISSION['nobody']['getSendDate'] = true;
    $PROC_PERMISSION['user']['getSendDate'] = true;

    $PROC_PERMISSION['user']['addItemWishList'] = true;
    $PROC_PERMISSION['user']['removeItemWishList'] = true;
    $PROC_PERMISSION['user']['createWishListNewByUser'] = true;
    $PROC_PERMISSION['user']['findWishList'] = true;
    $PROC_PERMISSION['user']['changeItemWishList'] = true;
    $PROC_PERMISSION['nobody']['valiateDataAmazonPay'] = true;
    $PROC_PERMISSION['user']['valiateDataAmazonPay'] = true;

    $PROC_PERMISSION['admin']['checkOrder'] = true;

    $PROC_PERMISSION['nobody']['updateOrder'] = true;
    $PROC_PERMISSION['user']['updateOrder'] = true;
    $PROC_PERMISSION['admin']['changeMultiItemRakuten'] = true;

    $PROC_PERMISSION['nobody']['getProductDefault'] = true;
    $PROC_PERMISSION['user']['getProductDefault'] = true;

    $PROC_PERMISSION['nobody']['changeColorProduct'] = true;
    $PROC_PERMISSION['user']['changeColorProduct'] = true;

    $PROC_PERMISSION['nobody']['removeProduct'] = true;
    $PROC_PERMISSION['user']['removeProduct'] = true;

    $PROC_PERMISSION['nobody']['pickSideProduct'] = true;
    $PROC_PERMISSION['user']['pickSideProduct'] = true;

    $PROC_PERMISSION['nobody']['changeTotalProduct'] = true;
    $PROC_PERMISSION['user']['changeTotalProduct'] = true;

    $PROC_PERMISSION['nobody']['searchProductsReport'] = true;
    $PROC_PERMISSION['user']['searchProductsReport'] = true;

    $PROC_PERMISSION['nobody']['goToShop'] = true;
    $PROC_PERMISSION['user']['goToShop'] = true;
    $PROC_PERMISSION['admin']['updateSendMailStatus'] = true;

    $PROC_PERMISSION['nobody']['findItemWebCategory'] = true;
    $PROC_PERMISSION['user']['findItemWebCategory'] = true;
    $PROC_PERMISSION['admin']['findItemWebCategory'] = true;

    $PROC_PERMISSION['admin']['approveReview'] = true;
    $PROC_PERMISSION['admin']['approveBattles'] = true;
    $PROC_PERMISSION['admin']['loadMoreItemComments'] = true;
    $PROC_PERMISSION['user']['loadMoreItemComments'] = true;
    $PROC_PERMISSION['nobody']['loadMoreItemComments'] = true;
    $PROC_PERMISSION['admin']['loadMoreBattleRank'] = true;
    $PROC_PERMISSION['user']['loadMoreBattleRank'] = true;
    $PROC_PERMISSION['nobody']['loadMoreBattleRank'] = true;
    $PROC_PERMISSION['nobody']['loadMoreItemComments'] = true;
    $PROC_PERMISSION['admin']['load_more_type_rank'] = true;
    $PROC_PERMISSION['user']['load_more_type_rank'] = true;
    $PROC_PERMISSION['nobody']['load_more_type_rank'] = true;
$PROC_PERMISSION['user']['loadMoreBattleMessages'] = true;
$PROC_PERMISSION['admin']['loadMoreBattleMessages'] = true;
$PROC_PERMISSION['nobody']['loadMoreBattleMessages'] = true;

    $PROC_PERMISSION['admin']['changeAdminstateItem'] = true;

    $PROC_PERMISSION['nobody']['getSendDateItemSameDay'] = true;
    $PROC_PERMISSION['user']['getSendDateItemSameDay'] = true;

    $PROC_PERMISSION['nobody']['getMoreItem'] = true;
    $PROC_PERMISSION['user']['getMoreItem'] = true;

    $PROC_PERMISSION['nobody']['drawSelectSizeAndColorByItem'] = true;
    $PROC_PERMISSION['user']['drawSelectSizeAndColorByItem'] = true;

    $PROC_PERMISSION['nobody']['drawSumStockItemBlank'] = true;
    $PROC_PERMISSION['user']['drawSumStockItemBlank'] = true;

    $PROC_PERMISSION['nobody']['drawPopupStockItemBlank'] = true;
    $PROC_PERMISSION['user']['drawPopupStockItemBlank'] = true;

    $PROC_PERMISSION['nobody']['applePayComm'] = true;
    $PROC_PERMISSION['user']['applePayComm'] = true;
    $PROC_PERMISSION['nobody']['saveApplePayToken'] = true;
    $PROC_PERMISSION['user']['saveApplePayToken'] = true;
    $PROC_PERMISSION['nobody']['checkExistedApplePayOrder'] = true;
    $PROC_PERMISSION['user']['checkExistedApplePayOrder'] = true;
    $PROC_PERMISSION['user']['getBottomButton'] = true;
    $PROC_PERMISSION['nobody']['getBottomButton'] = true;
    $PROC_PERMISSION['nobody']['minify'] = true;
    $PROC_PERMISSION['user']['minify'] = true;
    $PROC_PERMISSION['admin']['minify'] = true;

    $PROC_PERMISSION['user']['addRakutenID'] = true;
    $PROC_PERMISSION['nobody']['addRakutenID'] = true;

    $PROC_PERMISSION['nobody']['getRecommen1Item'] = true;
    $PROC_PERMISSION['user']['getRecommen1Item'] = true;

    $PROC_PERMISSION['nobody']['getInfoRecommen1Item'] = true;
    $PROC_PERMISSION['user']['getInfoRecommen1Item'] = true;

    $PROC_PERMISSION['nobody']['getInfoItemSelect'] = true;
    $PROC_PERMISSION['user']['getInfoItemSelect'] = true;

    $PROC_PERMISSION['nobody']['getDrawToolLinkChange'] = true;
    $PROC_PERMISSION['user']['getDrawToolLinkChange'] = true;

    $PROC_PERMISSION['admin']['changeThemeBuyState'] = true;

    $PROC_PERMISSION['admin']['addImgPreviewTheme'] = true;
    $PROC_PERMISSION['admin']['removeImgPreviewTheme'] = true;

    $PROC_PERMISSION['nobody']['directUrlPayment'] = true;
    $PROC_PERMISSION['user']['directUrlPayment'] = true;

    $PROC_PERMISSION['admin']['countUserSendMail'] = true;

    $PROC_PERMISSION['user']['cancelStepMail'] = true;
    $PROC_PERMISSION['nobody']['cancelStepMail'] = true;

    $PROC_PERMISSION['user']['getSellerReward'] = true;

    $PROC_PERMISSION['nobody']['getHtmlRecoment4Item'] = true;
    $PROC_PERMISSION['user']['getHtmlRecoment4Item'] = true;

    $PROC_PERMISSION['nobody']['getHtmlRecoment8Item'] = true;
    $PROC_PERMISSION['user']['getHtmlRecoment8Item'] = true;

    $PROC_PERMISSION['nobody']['getItemDetail'] = true;
    $PROC_PERMISSION['user']['getItemDetail'] = true;

    $PROC_PERMISSION['admin']['changeCartThank'] = true;

    $PROC_PERMISSION['user']['cancelMagazineMail'] = true;
    $PROC_PERMISSION['nobody']['cancelMagazineMail'] = true;

    $PROC_PERMISSION['user']['calculateTotalPrice'] = true;
    $PROC_PERMISSION['nobody']['calculateTotalPrice'] = true;

    $PROC_PERMISSION['user']['addCartItemBlank'] = true;
    $PROC_PERMISSION['nobody']['addCartItemBlank'] = true;

    $PROC_PERMISSION['user']['add_mask'] = true;
    $PROC_PERMISSION['nobody']['add_mask'] = true;

    $PROC_PERMISSION['nobody']['getInfoOrder'] = true;
    $PROC_PERMISSION['user']['getInfoOrder'] = true;

    $PROC_PERMISSION["nobody"]["findItemColor"] = true;
    $PROC_PERMISSION["nobody"]["get3ItemBestBy"] = true;

    $PROC_PERMISSION["user"]["findItemColor"] = true;
    $PROC_PERMISSION["user"]["get3ItemBestBy"] = true;

    $PROC_PERMISSION["user"]["findItemWebCategoryCompare"] = true;
    $PROC_PERMISSION["nobody"]["findItemWebCategoryCompare"] = true;

    $PROC_PERMISSION['nobody']['search_faq_hot'] = true;
    $PROC_PERMISSION['user']['search_faq_hot'] = true;

    $PROC_PERMISSION['nobody']['search_faq_hot2'] = true;
    $PROC_PERMISSION['user']['search_faq_hot2'] = true;

    $PROC_PERMISSION['nobody']['commentItem'] = true;
    $PROC_PERMISSION['admin']['commentItem'] = true;
    $PROC_PERMISSION['user']['commentItem'] = true;

    $PROC_PERMISSION['nobody']['loadItemComment'] = true;
    $PROC_PERMISSION['admin']['loadItemComment'] = true;
    $PROC_PERMISSION['user']['loadItemComment'] = true;
    $PROC_PERMISSION['user']['getTweets'] = true;
    $PROC_PERMISSION['nobody']['getTweets'] = true;
    $PROC_PERMISSION['user']['productSizes'] = true;
    $PROC_PERMISSION['nobody']['productSizes'] = true;
    $PROC_PERMISSION["factories"]["changePayDelivery"] = true;
    $PROC_PERMISSION["admin"]["addOrderPackage"] = true;
    $PROC_PERMISSION["factories"]["addOrderPackage"] = true;
    $PROC_PERMISSION["factories"]["changePackageStatus"] = true;
    $PROC_PERMISSION["admin"]["changePackageStatus"] = true;
    $PROC_PERMISSION["factories"]["downloadZipCsv"] = true;
    $PROC_PERMISSION["factories"]["updatePendingStatus"] = true;
    $PROC_PERMISSION["admin"]["update_delivery_state"] = true;
    $PROC_PERMISSION["factories"]["update_delivery_state"] = true;

    $PROC_PERMISSION['nobody']['getItemWeb'] = true;
    $PROC_PERMISSION['user']['getItemWeb'] = true;

    $PROC_PERMISSION['admin']['getInputAssetTemplate'] = true;

    $PROC_PERMISSION['user']['getPreviews'] = true;
    $PROC_PERMISSION['nobody']['getPreviews'] = true;
    $PROC_PERMISSION['admin']['getPreviews'] = true;
    $PROC_PERMISSION['user']['previewAble'] = true;
    $PROC_PERMISSION['nobody']['previewAble'] = true;
    $PROC_PERMISSION['admin']['previewAble'] = true;
    $PROC_PERMISSION['admin']['addTemplateAsset'] = true;

    $PROC_PERMISSION['admin']['getListSideMasterItem'] = true;
    $PROC_PERMISSION['admin']['getListColorMasterItem'] = true;
    $PROC_PERMISSION["admin"]["getS3UrlFromFile"] = true;
    $PROC_PERMISSION["admin"]["getS3UrlFromImage"] = true;
    $PROC_PERMISSION["admin"]["getScaleSizeItemType"] = true;
    $PROC_PERMISSION["user"]["getS3UrlFromImage"] = true;

    $PROC_PERMISSION["user"]["getListItemRecommend"] = true;
    $PROC_PERMISSION["user"]["getListItemTypeAddItem"] = true;
    $PROC_PERMISSION["user"]["AddItemTypeInput"] = true;
    $PROC_PERMISSION["user"]["getDesignByImage"] = true;
    $PROC_PERMISSION["user"]["EditItemTypeInput"] = true;

    $PROC_PERMISSION['admin']['cancelOrderPeri'] = true;
    $PROC_PERMISSION['user']['user_cancel_order_3rd'] = true;

    $PROC_PERMISSION['admin']['downCsvShippingJapan'] = true;
    $PROC_PERMISSION['admin']['downloadCsv3rdMask'] = true;

    $PROC_PERMISSION['admin']['csvSurvey'] = true;

$PROC_PERMISSION['admin']['willmail'] = true;
$PROC_PERMISSION['admin']['csvWillmailList'] = true;
$PROC_PERMISSION['admin']['addListWillMail'] = true;
$PROC_PERMISSION['admin']['willmail_list'] = true;
$PROC_PERMISSION['admin']['createListWillmail'] = true;
$PROC_PERMISSION['admin']['updateWillmailTarget'] = true;
$PROC_PERMISSION['admin']['editListWillMail'] = true;
$PROC_PERMISSION['admin']['addItemWillmail'] = true;


$PROC_PERMISSION["user"]["goToDesignCustomTool"] = true;
$PROC_PERMISSION["user"]["callbackAddItem"] = true;


$PROC_PERMISSION['admin']['changePickUpTwitter'] = true;

$PROC_PERMISSION['admin']['changePickUpTwitter'] = true;
$PROC_PERMISSION['admin']['downloadCsvSaleCampaign'] = true;

$PROC_PERMISSION['nobody']['get_statistics'] = true;
$PROC_PERMISSION['admin']['get_statistics'] = true;
$PROC_PERMISSION['user']['get_statistics'] = true;

$PROC_PERMISSION['user']['loadMoreCbox'] = true;
$PROC_PERMISSION['nobody']['loadMoreCbox'] = true;
$PROC_PERMISSION['user']['changeCate'] = true;
$PROC_PERMISSION['nobody']['changeCate'] = true;
$PROC_PERMISSION['nobody']['loadMoreCbox'] = true;
$PROC_PERMISSION['admin']['loadMoreCbox'] = true;
$PROC_PERMISSION['admin']['changeFaqItemPageState'] = true;
$PROC_PERMISSION['admin']['changeCategoryState'] = true;
$PROC_PERMISSION['admin']['deleteBanner'] = true;

$PROC_PERMISSION['admin']['update_item_ranking'] = true;

$PROC_PERMISSION['nobody']['changeRanking'] = true;
$PROC_PERMISSION['user']['changeRanking'] = true;

$PROC_PERMISSION['admin']['changeMasterCategoryTopPage'] = true;
$PROC_PERMISSION['admin']['changeTopPageOrder'] = true;

$PROC_PERMISSION['admin']['downloadCsvGoogleProductCategory'] = true;

$PROC_PERMISSION['nobody']['getCategoryTopPage'] = true;
$PROC_PERMISSION['user']['getCategoryTopPage'] = true;