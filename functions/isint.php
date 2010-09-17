<?

function isint( $mixed )
{
	return ( preg_match( '/^\d*$/'  , $mixed) == 1 );
}
