$(document).ready(function () {
    var url = '/proc.php?run=changeRanking&ctagory_id=';

    $('#item_category_id').on('change', function () {
        var category_id = $('#item_category_id').val()
        changeRank(url + category_id)
    });

    $(".list-cate-ranking div.item").click(function(){
        var current_id = $("#title-item-ranking").data('id')
        var id = $(this).attr("data-value");
        if (current_id != id) {
            changeRank(url + id)
            $("#title-item-ranking").data('id', id)
            $("#title-item-ranking").text($(this).text())
            console.log(id)
        }
    });

    $(".category-more").on("click", function () {
        $.getJSON('/proc.php?run=getCategoryTopPage', function (data) {
            $('.list-category-item-details').append(data);
            $(".category-more").remove();
        });
    })
});

function changeRank(url) {
    $.getJSON(url, function (data) {
        $('.list-ranking-cate').html(data);
    });
}