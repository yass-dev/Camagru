<?php

function imagecreatefromfile($filename)
{
	if (!file_exists($filename))
		return null;

	$infos = getimagesize($filename);
	if (!isset($infos['mime']))
		return null;

	$type = $infos['mime'];
	if (!str_starts_with($type, "image/"))
		return null;
	return imagecreatefromstring(file_get_contents($filename));
}

?>