<div class="gallery_list">
	<?php
		foreach ($parameters['publications'] as $publication)
			include('views/gallerie/gallery_item.view.php');
	?>
</div>

<script>

document.getElementsByClassName('gallerie_item');

</script>

<style>

.gallery_list
{
	display: flex;
	flex-direction: column;
	align-items: center;
	width: fit-content;
	margin: 0 auto;
}

</style>