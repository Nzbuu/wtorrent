<?php
/*
This file is part of wTorrent.

wTorrent is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

wTorrent is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Class done by David Marco Martinez
*/
/**
 * Verifica si un string esta codificado en UTF-8
 *
 * @param string $str
 * @return bool
 */
function is_UTF8( $str )
{
	return preg_match( '%(?:
		[\xC2-\xDF][\x80-\xBF]				# non-overlong 2-byte
		|\xE0[\xA0-\xBF][\x80-\xBF]			# excluding overlongs
		|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}	# straight 3-byte
		|\xED[\x80-\x9F][\x80-\xBF]			# excluding surrogates
		|\xF0[\x90-\xBF][\x80-\xBF]{2}		# planes 1-3
		|[\xF1-\xF3][\x80-\xBF]{3}			# planes 4-15
		|\xF4[\x80-\x8F][\x80-\xBF]{2}		# plane 16
		)+%xs', $str );
}

function _unescape_internal($var)
{
	switch (gettype($var)) {
	case 'array':
		return array_map('_unescape_internal', $var);
	case 'string':
		return stripslashes($var);
	default:
		return $var;
	}
}
function unescape( $var )
{
	if (!get_magic_quotes_gpc())
	{
		return;
	}
	return _unescape_internal($var);
}
function html( $str )
{
	$str = str_replace( array( '�', '�' ), array( '&ntilde;', '&Ntilde;' ), $str );
	return preg_replace( '/[^\x00-\x7F]/e', '"&#".ord("$0").";"', $str );

	return str_replace( array_keys( getHtmlChars( ) ), getHtmlChars( ), $str );
}

function getHtmlChars( )
{
	return array(
	'!' => '&#33;',
	'"' => '&quot;',
	'#' => '&#35;',
	'$' => '&#36;',
	'%' => '&#37;',
	'&' => '&amp;',
	'\'' => '&#39;',
	'(' => '&#40;',
	')' => '&#41;',
	'*' => '&#42;',
	'+' => '&#43;',
	',' => '&#44;',
	'-' => '&#45;',
	'.' => '&#46;',
	'/' => '&#47;',
	':' => '&#58;',
	';' => '&#59;',
	'<' => '&lt;',
	'=' => '&#61;',
	'>' => '&gt;',
	'?' => '&#63;',
	'@' => '&#64;',
	'[' => '&#91;',
	'\\' => '&#92;',
	']' => '&#93;',
	'^' => '&#94;',
	'_' => '&#95;',
	'`' => '&#96;',
	'{' => '&#123;',
	'|' => '&#124;',
	'}' => '&#125;',
	'~' => '&#126;',
	'�' => '&#128;',
	'�' => '&#130;',
	'�' => '&#140;',
	'�' => '&#142;',
	'�' => '&#145;',
	'�' => '&#146;',
	'�' => '&#147;',
	'�' => '&#148;',
	'�' => '&#149;',
	'�' => '&#150;',
	'�' => '&#151;',
	'�' => '&#152;',
	'�' => '&#153;',
	'�' => '&#154;',
	'�' => '&#155;',
	'�' => '&#156;',
	'�' => '&#159;',
	'�' => '&iexcl;',
	'�' => '&cent;',
	'�' => '&pound;',
	'�' => '&curren;',
	'�' => '&yen;',
	'�' => '&brvbar;',
	'�' => '&sect;',
	'�' => '&uml;',
	'�' => '&copy;',
	'�' => '&ordf;',
	'�' => '&laquo;',
	'�' => '&not;',
	'�' => '&shy;',
	'�' => '&reg;',
	'�' => '&macr;',
	'�' => '&deg;',
	'�' => '&plusmn;',
	'�' => '&sup2;',
	'�' => '&sup3;',
	'�' => '&acute;',
	'�' => '&micro;',
	'�' => '&para;',
	'�' => '&middot;',
	'�' => '&cedil;',
	'�' => '&sup1;',
	'�' => '&ordm;',
	'�' => '&raquo;',
	'�' => '&frac14;',
	'�' => '&frac12;',
	'�' => '&frac34;',
	'�' => '&iquest;',
	'�' => '&Agrave;',
	'�' => '&Aacute;',
	'�' => '&Acirc;',
	'�' => '&Atilde;',
	'�' => '&Auml;',
	'�' => '&Aring;',
	'�' => '&AElig;',
	'�' => '&Ccedil;',
	'�' => '&Egrave;',
	'�' => '&Eacute;',
	'�' => '&Ecirc;',
	'�' => '&Euml;',
	'�' => '&Igrave;',
	'�' => '&Iacute;',
	'�' => '&Icirc;',
	'�' => '&Iuml;',
	'�' => '&ETH;',
	'�' => '&Ntilde;',
	'�' => '&Ograve;',
	'�' => '&Oacute;',
	'�' => '&Ocirc;',
	'�' => '&Otilde;',
	'�' => '&Ouml;',
	'�' => '&times;',
	'�' => '&Oslash;',
	'�' => '&Ugrave;',
	'�' => '&Uacute;',
	'�' => '&Ucirc;',
	'�' => '&Uuml;',
	'�' => '&Yacute;',
	'�' => '&THORN;',
	'�' => '&szlig;',
	'�' => '&agrave;',
	'�' => '&aacute;',
	'�' => '&acirc;',
	'�' => '&atilde;',
	'�' => '&auml;',
	'�' => '&aring;',
	'�' => '&aelig;',
	'�' => '&ccedil;',
	'�' => '&egrave;',
	'�' => '&eacute;',
	'�' => '&ecirc;',
	'�' => '&euml;',
	'�' => '&igrave;',
	'�' => '&iacute;',
	'�' => '&icirc;',
	'�' => '&iuml;',
	'�' => '&eth;',
	'�' => '&ntilde;',
	'�' => '&ograve;',
	'�' => '&oacute;',
	'�' => '&ocirc;',
	'�' => '&otilde;',
	'�' => '&ouml;',
	'�' => '&divide;',
	'�' => '&oslash;',
	'�' => '&ugrave;',
	'�' => '&uacute;',
	'�' => '&ucirc;',
	'�' => '&uuml;',
	'�' => '&yacute;',
	'�' => '&thorn;',
	'�' => '&yuml;',
	'�' => '&OElig;',
	'�' => '&oelig;',
	'�' => '&Scaron;',
	'�' => '&scaron;',
	'�' => '&Yuml;',
	'�' => '&circ;',
	'�' => '&tilde;',
	'�' => '&ndash;',
	'�' => '&mdash;',
	'�' => '&lsquo;',
	'�' => '&rsquo;',
	'�' => '&sbquo;',
	'�' => '&ldquo;',
	'�' => '&rdquo;',
	'�' => '&bdquo;',
	'�' => '&dagger;',
	'�' => '&Dagger;',
	'�' => '&permil;',
	'�' => '&lsaquo;',
	'�' => '&rsaquo;' );
}
?>
