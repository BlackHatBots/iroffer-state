<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
#
# Copyright 2004 Dirk Meyer, Im Grund 4, 34317 Habichstwald
#	dirk.meyer@dinoex.sub.org
#
# Updates on:
#	http://anime.dinoex.net/xdcc/tools/
#

# IRC-Nick des Bots
$nick = ereg_replace( '/[^/]*[.]php$', '', $_SERVER[ 'PHP_SELF' ] );
$nick = ereg_replace( '^/(.*/)*', '', $nick );
#$nick = 'XDCC|'.$nick;
#$nick = 'XDCC|irofferbot';

# force to show no group
#$_GET[ 'group' ] = '*';

# Statusfiles der bots
$filenames = array(
	'mybot.xdcc',
);

$cache_file = 'size.data';
$base_path = './';

$javascript = 1;

$strip_in_names = array (
	'^ *- *',
	"\002",
	"\0030[,]0",
	"\0030[,]5",
	"\0030",
	"\00312",
	"\00314",
	"\00315",
	"\0033",
	"\0034",
	"\0035\037",
	"\0037",
	"\0032",
	"\00310",
	"\003",
	"\017",
);

?>
<html>
<head>
<meta name="generator" content="iroffer-xdcc 0.7, iroffer.dinoex.net">
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<meta http-equiv="content-language" content="de-de">
<link rel="icon" href="/favicon.ico">
<link rel="stylesheet" type="text/css" href="iroffer-state.css">
<title><?php echo $nick; ?></title>
<?php
if ( $javascript > 0 ) {
	echo '
<script language=javascript type=text/javascript>
<!--
function selectThis(src) {
    document.selection.clear;
    txt = eval(src +".innerText");
    theObj = document.all(txt);
    txtRange = document.body.createTextRange();
    txtRange.moveToElementText(eval(src));
    txtRange.select();
    txtRange.execCommand("RemoveFormat");
    txtRange.execCommand("Copy");
    alert(txt + " wurde in die Zwischenablage kopiert");
}
-->
</script>
	';
}
?>
</head>
<body>
<center>

<?php

#
# bytes in lesbarere Form ausgeben.
#
function makesize( $nbytes ) {
	global $debug;

	if ( $nbytes < 1000 ) {
		return sprintf( '%db', $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dk', $nbytes );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $debug != '' ) {
		return sprintf( '%dM', $nbytes );
	}
	if ( $nbytes < 1000 ) {
		return sprintf( '%dM', $nbytes );
	}
	if ( $nbytes < 10000 ) {
		return sprintf( '%.1fG', $nbytes / 1024 );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dG', $nbytes );
	}
	if ( $nbytes < 10000 ) {
		return sprintf( '%.1fT', $nbytes / 1024 );
	}
	$nbytes = ( $nbytes + 512 ) / 1024;
	if ( $nbytes < 1000 ) {
		return sprintf( '%dT', $nbytes );
	}
	return sprintf( '%dE', $nbytes );
}

function clean_names( $text2 ) {
	global $strip_in_names;

	foreach ( $strip_in_names as $skey => $sdata) {
		$text2 = ereg_replace( $sdata, '', $text2 );
	}
	return $text2;
}

function read_sizecache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;

	$sizecache_dirty = 0;
	$len = filesize($filename);
	if ( $len <= 0 ) 
		return;
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$tread = fread($fp, $len);
		fclose($fp);
		$tlines = explode("\n", $tread);
		foreach ( $tlines as $ykey => $ydata) {
			if ( ereg( '[:]', $ydata ) ) {
				list( $key, $tsize ) = explode(':', $ydata, 2);
				if ( $tsize > 0 )
					$sizecache[ $key ] = $tsize;
			}
		}
	}
}

function write_sizecache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;

	if ( $sizecache_dirty == 0 )
		return;
	$fp = fopen( $filename, 'w' );
	if ( $fp ) {
		foreach ( $sizecache as $key => $ydata ) {
			fwrite( $fp, $key.':'.$ydata."\n" );
		}
		fclose($fp);
	}
}

function filesize_cache( $filename ) {
	global $sizecache;
	global $sizecache_dirty;
	global $base_path;

	if ( isset( $sizecache[ $filename ] ) ) {
		return $sizecache[ $filename ];
	}
	$localfile = $filename;
	if ( !ereg( '^/', $filename ) )
		$localfile = $base_path.$filename;
	$tsize = filesize( $localfile );
	$sizecache[ $filename ] = $tsize;
	$sizecache_dirty ++;
	return $tsize;
}

function cgi_escape( $string ) {
	$string = ereg_replace( '[&]', '%26', $string );
	$string = ereg_replace( '[+]', '%2B', $string );
	return $string;
}

function make_self_more() {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($_GET[ 'group' ]);
		$par ++;
	}
	if ( !isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen=1';
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function make_self_order( $order ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'group' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($_GET[ 'group' ]);
		$par ++;
	}
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( $order != '' ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$order;
		$par ++;
	}
	return $link;
}

function make_self_group( $group ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( $group != '' ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'group='.cgi_escape($group);
		$par ++;
	}
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

function make_self_back( $order ) {
	$par = 0;
	$link = $_SERVER[ 'PHP_SELF' ];
	# options:
	if ( isset( $_GET[ 'volumen' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'volumen='.$_GET[ 'volumen' ];
		$par ++;
	}
	if ( isset( $_GET[ 'order' ] ) ) {
		if ( $par == 0 )
			$link .= '?';
		else
			$link .= '&amp;';
		$link .= 'order='.$_GET[ 'order' ];
		$par ++;
	}
	return $link;
}

read_sizecache( $cache_file );

$read = '';
# Status aller Bots lesen
foreach ( $filenames as $key => $filename) {
	$fp = fopen( $filename, 'r' );
	if ( $fp ) {
		$read .= fread($fp, filesize ($filename));
		fclose($fp);
	}
}

$nick2 = ereg_replace( '[^A-Za-z_0-9]', '', $nick );
$packs = 0;
$fpacks = 0;
$total[ 'packs' ] = 0;
$total[ 'size' ] = 0;
$total[ 'downl' ] = 0;
$total[ 'xx_gets' ] = 0;
$total[ 'trans' ] = 0;
$gruppen[ '*' ][ 'packs' ] = 0;
$gruppen[ '*' ][ 'size' ] = 0;
$gruppen[ '*' ][ 'xx_gets' ] = 0;
$gruppen[ '*' ][ 'trans' ] = 0;

$newfile = 0;
$datalines = explode("\n", $read);
foreach ( $datalines as $key => $data) {
	if ( $data == '' )
		continue;

	if ( ereg( '^Do Not Edit This File[:] ', $data ) ) {
		list( $key, $text ) = explode(': ', $data, 2);
		list( $irec, $iband, $itotal, $irest ) = explode(' ', $text, 4);
		$total[ 'downl' ] += $itotal;
		$packs = 0;
		continue;
	}
	if ( !ereg( ' ', $data ) )
		continue;

	list( $key, $text ) = explode(' ', $data, 2);
	if ( $text == '' )
		continue;

	# removed packages
	if ( $irest == '-' ) {
		if ( $key == 'xx_file' ) {
			$fsize = filesize_cache( $text );
			if ( isset( $seen[ $text ] ) )
				continue;
			$seen[ $text ] = 0;
			$total[ 'packs' ] ++;
			$total[ 'size' ] += $fsize;
		}
		if ( $key == 'xx_gets' ) {
			$total[ 'xx_gets' ] += $text;
			$total[ 'trans' ] += $fsize * $text;
		}
		continue;
	}

	if ( $key == 'xx_file' ) {
		$newfile = 0;
		$packs ++;
		$fsize = filesize_cache( $text );
		$info[ $packs ][ 'pack' ] = $packs;
		$info[ $packs ][ 'size' ] = $fsize;
		if ( !isset( $info[ $packs ][ 'xx_gets' ] ) ) {
			$info[ $packs ][ 'xx_gets' ] = 0;
			$info[ $packs ][ 'trans' ] = 0;
		}
		if ( !isset( $seen[ $text ] ) ) {
			$newfile = 1;
			$seen[ $text ] = $packs;
			$total[ 'packs' ] ++;
			$total[ 'size' ] += $fsize;
		}
		$fpacks = $seen[ $text ];
	}

	if ( $key == 'xx_gets' ) {
		$tgets = $text;
		$ttrans = $info[ $fpacks ][ 'size' ] * $tgets;
		$info[ $fpacks ][ $key ] += $tgets;
		$info[ $fpacks ][ 'trans' ] += $ttrans;
		$total[ 'xx_gets' ] += $tgets;
		$total[ 'trans' ] += $ttrans;
		continue;
	}
	if ( $key == 'xx_desc' ) {
		$info[ $fpacks ][ 'xx_desc' ] = clean_names( $text );
		continue;
	}

	if ( !isset( $info[ $fpacks ][ $key ] ) ) {
		$info[ $fpacks ][ $key ] = $text;
	}

	if ( $key == 'xx_trno' ) {
		if ( !isset( $gruppen[ $gr ][ 'xx_trno' ] ) ) {
			$gruppen[ $gr ][ 'xx_trno' ] = clean_names( $text );
		}
		continue;
	}
	if ( $key == 'xx_note' ) {
		$info[ $fpacks ][ 'xx_note' ] = clean_names( $text );
		continue;
	}

	if ( $key != 'xx_data' )
		continue;

	$gr = $text;
	if ( !isset( $gruppen[ $gr ][ 'packs' ] ) ) {
		$gruppen[ $gr ][ 'packs' ] = 0;
		$gruppen[ $gr ][ 'size' ] = 0;
		$gruppen[ $gr ][ 'xx_gets' ] = 0;
		$gruppen[ $gr ][ 'trans' ] = 0;
	}
	$gruppen[ $gr ][ 'xx_gets' ] += $tgets;
	$gruppen[ $gr ][ 'trans' ] += $ttrans;
	$gruppen[ '*' ][ 'xx_gets' ] += $tgets;
	$gruppen[ '*' ][ 'trans' ] += $ttrans;
	if ( $newfile == 0 )
		continue;

	$gruppen[ $gr ][ 'packs' ] ++;
	$gruppen[ $gr ][ 'size' ] += $info[ $fpacks ][ 'size' ];
	$gruppen[ '*' ][ 'packs' ] ++;
	$gruppen[ '*' ][ 'size' ] += $info[ $fpacks ][ 'size' ];
}

write_sizecache( $cache_file );

# Ueberschrift:
if ( isset( $_GET[ 'group' ] ) ) {
	echo '<h1>'.$nick." Datei-Liste</h1>\n";
	echo "\n";
	echo '<p>Download im IRC mit <span class="cmd">/msg '.$nick.' xdcc send #nummer</span></p>';
	echo "\n";
} else {
	echo '<h1>'.$nick." Gruppen-Liste</h1>\n";
	echo "\n";
}

?>

<table cellpadding="2" cellspacing="0" summary="list">
<thead>

<?php


if ( isset( $_GET[ 'group' ] ) ) {
	$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.make_self_order( '' ).'">PACK</a>';
	$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hsize = '<a class="head" title="sortieren nach G��e der Files"
href="'.make_self_order( 'size' ).'">GR�SSE</a>';

	if ( !isset( $_GET[ 'order' ] ) ) {
		foreach ( $info as $key => $data)
			$ausgabe[ $key ] = $key;
		asort( $ausgabe );
		$hpack = 'PACK';
	} else {
		$ofound = 0;
		if ( $_GET[ 'order' ] == 'gets' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'xx_gets' ];
			arsort( $ausgabe );
			$hgets = 'DLs';
			$ofound = 1;
		}
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $info[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GR�SSE';
			$ofound = 1;
		}
		if ( $ofound == 0 ) {
			foreach ( $info as $key => $data)
				$ausgabe[ $key ] = $key;
			asort( $ausgabe );
			$hpack = 'PACK';
		}
	}
	$linkmore = '&nbsp;<a title="zur�ck" href="'.make_self_back( '' ).'">(zur�ck)</a>';

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
<th class="head">'.$hsize.'</th>
<th class="head">BESCHREIBUNG'.$linkmore.'</th>
</tr>
</thead>
';

	$gr = $_GET[ 'group' ];
	$tpacks = $gruppen[ $gr ][ 'packs' ];
	$tsize = $gruppen[ $gr ][ 'size' ];

	echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$gruppen[ $gr ][ 'xx_gets' ].'</th>
<th class="right">'.makesize($tsize).'</th>
<th class="head">['.makesize($gruppen[ $gr ][ 'trans' ]).'] vollst�ndig heruntergeladen</th>
</tr>
</tfoot>
<tbody>
';

	foreach ( $ausgabe as $key => $data) {
		if ( $key == '' )
			continue;
		if ( ( $_GET[ 'group' ] != '*' )
		&& ( $info[ $key ][ 'xx_data' ] != $_GET[ 'group' ] ) )
			continue;

		$tpack = $info[ $key ][ 'pack' ];
		$tname = $info[ $key ][ 'xx_desc' ];
		$jsid= $nick2.'_'.$tpack;

		$tname = htmlspecialchars( $tname);
		if ( $javascript > 0 ) {
			$tname = '<span class="selectable" onclick=javascript:selectThis(\''.
				$jsid.'\');>'.
				$tname."</span>\n".
				'<span id="'.$jsid.'" class="hidden">'.
				'/msg '.$nick.' xdcc send #'.$tpack."</span>\n";
		}
		if ( isset( $info[ $key ][ 'xx_note' ] ) )
			$tname .= '<br>'.$info[ $key ][ 'xx_note' ];

		$label = "Download mit:\n/msg ".$nick.' xdcc send #'.$tpack."\n";
		echo '
<tr>
<td class="right">#'.$tpack.'</td>
<td class="right">'.$info[ $key ][ 'xx_gets' ].'</td>
<td class="right">'.makesize($info[ $key ][ 'size' ]).'</td>
<td class="content" title="'.$label.'">'.$tname.'</td>
</tr>
';
	}

} else {
	$hpack = '<a class="head" title="sortieren nach Pack-Nr."
href="'.make_self_order( 'pack' ).'">PACKs</a>';
	$hgets = '<a class="head" title="sortieren nach Anzahl Downloads"
href="'.make_self_order( 'gets' ).'">DLs</a>';
	$hrget = '<a class="head" title="sortieren nach Downloads per Datei"
href="'.make_self_order( 'rget' ).'">DLs/Pack</a>';
	$hsize = '<a class="head" title="sortieren nach G��e der Files"
href="'.make_self_order( 'size' ).'">GR�SSE</a>';
	$htvol = '<a class="head" title="sortieren nach �bertragusngsvolumen"
href="'.make_self_order( 'tvol' ).'">Volumen</a>';
	$hname = '<a class="head" title="sortieren nach Gruppe"
href="'.make_self_order( '' ).'">GRUPPE</a>';

	if ( !isset( $_GET[ 'order' ] ) ) {
		foreach ( $gruppen as $key => $data)
			$ausgabe[ $key ] = $key;
		asort( $ausgabe );
		$hname = 'GRUPPE';
	} else {
		if ( $_GET[ 'order' ] == 'pack' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'packs' ];
			arsort( $ausgabe );
			$hpack = 'PACKs';
		}
		if ( $_GET[ 'order' ] == 'gets' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'xx_gets' ];
			arsort( $ausgabe );
			$hgets = 'DLs';
		}
		if ( $_GET[ 'order' ] == 'rget' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'xx_gets' ] / $gruppen[ $key ][ 'packs' ];
			arsort( $ausgabe );
			$hrget = 'DLs/Pack';
		}
		if ( $_GET[ 'order' ] == 'size' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'size' ];
			arsort( $ausgabe );
			$hsize = 'GR�SSE';
		}
		if ( $_GET[ 'order' ] == 'tvol' ) {
			foreach ( $gruppen as $key => $data)
				$ausgabe[ $key ] = $gruppen[ $key ][ 'trans' ];
			arsort( $ausgabe );
			$htvol = 'Volumen';
		}
	}

	$tvol1 = '';
	$rget1 = '';
	if ( isset( $_GET[ 'volumen' ] ) ) {
		$tvol1 = '<th class="head">'.$htvol.'</th>';
		$rget1 = '<th class="head">'.$hrget.'</th>';
		$linkmore = '&nbsp;<a title="Volumen ausblenden" href="'.make_self_more().'">(weniger)</a>';
	} else {
		$linkmore = '&nbsp;<a title="Volumen anzeigen" href="'.make_self_more().'">(mehr)</a>';
	}

	echo '
<tr>
<th class="head">'.$hpack.'</th>
<th class="head">'.$hgets.'</th>
'.$rget1.'
<th class="head">'.$hsize.'</th>
'.$tvol1.'
<th class="head">'.$hname.'</th>
<th class="head">BESCHREIBUNG'.$linkmore.'</th>
</tr>
</thead>
';

	$tpacks = $total[ 'packs' ];
	$tsize = $total[ 'size' ];
	$part = $total[ 'downl' ] - $total[ 'trans' ];
	$tcount = count($gruppen) - 1;

	$tvol2 = '';
	$rget2 = '';
	if ( isset( $_GET[ 'volumen' ] ) ) {
		$tvol2 = '<th class="right">'.makesize($total[ 'trans' ]).'</th>';
		$getsperpack = sprintf( '%.1f', $total[ 'xx_gets' ] / $tpacks );
		$rget2 = '<th class="right">'.$getsperpack.'</th>';
	}

	echo '
<tfoot>
<tr>
<th class="right">'.$tpacks.'</th>
<th class="right">'.$total[ 'xx_gets' ].'</th>
'.$rget2.'
<th class="right">'.makesize($tsize).'</th>
'.$tvol2.'
<th class="head">'.$tcount.'</th>
<th class="head"><a title="alle Packs in einer Liste anzeigen" href="'.make_self_group( '*' ).'">alle Packs</a> ['.makesize($total[ 'trans' ]).'] vollst�ndig heruntergeladen, ['.makesize($part).']&nbsp;unvollst�ndig</th>
</tr>
</tfoot>
<tbody>
';

	foreach ( $ausgabe as $key => $data) {
		if ( $key == '' )
			continue;
		if ( $key == '*' )
			continue;

		$tpacks= $gruppen[ $key ][ 'packs' ];
		$asize = $gruppen[ $key ][ 'size' ];
		$tsize = $gruppen[ $key ][ 'trans' ];
		$tname = $key;
		if ( isset( $gruppen[ $key ][ 'xx_trno' ] ) )
			$tname = $gruppen[ $key ][ 'xx_trno' ];
		$link = make_self_group( $key );

		$tvol3 = '';
		$rget3 = '';
		if ( isset( $_GET[ 'volumen' ] ) ) {
			$tvol3 = '<td class="right">'.makesize($tsize).'</td>';
			$getsperpack = sprintf( '%.1f', $gruppen[ $key ][ 'xx_gets' ] / $tpacks );
			$rget3 = '<td class="right">'.$getsperpack.'</td>';
		}
		echo '
<tr>
<td class="right">'.$tpacks.'</td>
<td class="right">'.$gruppen[ $key ][ 'xx_gets' ].'</td>
'.$rget3.'
<td class="right">'.makesize($asize).'</td>
'.$tvol3.'
<td class="content">'.htmlspecialchars($key).'</td>
<td class="content"><a title="Liste dieser Packs anzeigen" href="'.$link.'">'.htmlspecialchars($tname).'</a></td>
</tr>
';
	}

}

?>

</tbody>
</table>
</center>
</body>
</html>

