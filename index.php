<?php
include 'connect.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Family-Tree</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="tree">
		<?php
			$query = $pdo->prepare("SELECT * FROM `tree`");
			$query->execute();
			$data = $query->fetchAll(PDO::FETCH_ASSOC);

			function tree($data, $mom = 0) {
				echo '<ul>';
				foreach($data as $row){
					if($row['parenthood'] == $mom){
						echo '<li id="f'.$row["id"].'">';
						echo '<a class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal" data-id="'.$row["id"].'">'.$row['name']."</a>";
						tree($data, $row['id']);
						echo '</li>';
					}
				}
				echo '</ul>';
			}
			tree($data);
		?>
	</div>
	
	<!-- The Modal -->
	<div class="modal fade" id="myModal">
		<div class="modal-dialog modal-dialog-centered" style="width: 200px">
			<div class="modal-content">
                <input type="text" class="form-control" id="usr">
				<button id="add-child" type="button" class="fd-modal btn btn-primary">Add Child</button>
				<button id="add-parent" type="button" class="fd-modal btn btn-primary">Add Parent</button>
				<button id="delete" type="button" class="fd-modal btn btn-danger">Single Delete</button>
				<button id="delete-all" type="button" class="fd-modal btn btn-danger">All Delete</button>
			</div>
		</div>
	</div>	
</body>
</html>

<script>
	$('ul:empty').remove();

	$(document).on('click', 'a', function () {
        let data_id = $(this).attr('data-id');
        $('.fd-modal').attr('data-id', data_id);
    });

    // ADD CHILD
	$(document).on('click', '#add-child', function () {
        let dataId = $(this).attr('data-id');
        let name = $('#usr').val();
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {name: name, data_id: dataId},
            dataType: "json",
            success: function (result) {
                if(result.status == "successfully added!") {

                    if(result.count > 0)  {
                        // demeli var
                        $('a[data-id='+dataId+']').next('ul').append(`
                           <li id="f`+result.id+`"><a class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal" data-id=`+result.id+`>`+name+`</a></li>`);
                    }
                    if(result.count == 0){
                        $('a[data-id='+dataId+']').after(`
                        <ul><li id="f`+result.id+`"><a class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal" data-id=`+result.id+`>`+name+`</a></li></ul>`);
                    }
                    $('#usr').val("");
                }
            }
        });
    });

    // DELETE ALL
    $(document).on('click', '#delete-all', function () {
        let dataId = $(this).attr('data-id');
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {dataid: dataId},
            dataType: "json",
            success: function (result) {
                if(result.status == "successfully deleted!") {
                    $('a[data-id='+dataId+']').parent().remove();
                }
            }
        });
        $('ul:empty').remove();
    });

    // DELETE SINGLE
    $(document).on('click', '#delete', function () {
        let dataId = $(this).attr('data-id');
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {data: dataId},
            dataType: "json",
            success: function (result) {
                if(result.status == "deleted!") {
                    if(result.count > 0){
                        let asd = $('li[id=f'+dataId+']').children('ul').html();
                        $('li[id=f'+result.parent+']').children('ul').append(asd);
                        $('li[id=f'+dataId+']').remove();
                    }
                    else if(result.count == 0){
                        $('li[id=f'+dataId+']').remove();
                    }
                    $('ul:empty').remove();
                }
            }
        });
        $('ul:empty').remove();
    });

    // ADD PARENT
    $(document).on('click', '#add-parent', function () {
        let dataId = $(this).attr('data-id');
        let name = $('#usr').val();
        $.ajax({
            method: "POST",
            url: "ajax.php",
            data: {addData_id: dataId, prnt_name: name},
            dataType: "json",
            success: function (result) {
                if(result.status == "parent added!"){
                    if(result.count > 0) {
                        $('li[id=f' + result.child + ']').parent('ul').wrap(`<ul><li id="f` + result.id + `"></li></ul>`);
                        $('li[id=f' + result.id + ']').prepend(`
                        <a class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal" data-id=` + result.id + `>` + name + `</a>`);
                    }
                    if(result.count == 0){
                        $('a[data-id='+dataId+']').after(`
                        <ul><li id="f`+result.id+`"><a class="btn btn-outline-dark" data-toggle="modal" data-target="#myModal" data-id=`+result.id+`>`+name+`</a></li></ul>`);
                    }
                    $('#usr').val("");
                }

            }
        });
    });
</script>
