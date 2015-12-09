<?php
require_once "libdb.php"
require_once "liblog.php";

class MenuNow extends MenuLog{
  public __construct( $__host, $__user, $__passwd, $__uid ){
    parent::__construct( $__host, $__user, $__passwd, $__uid );
    $query = "CREATE TABLE UN".$__uid." ( Menu_Name text, Kind_Name varchar(10), Date DATE );";
    $result = $this->_db_throw_query( "Users_Geo", $query );
  }

  /* public method */
  /* method that resist menu log to db */
  public fuction ResistMenuLog( $_uid, $_log ){
    $query = "TRUNCATE TABLE UN".$_uid.";";
    $result = $this->_db_throw_query( "Users_Geo", $query );

    $query = "";
    $tmp = "INSERT INTO UN".$_uid." VALUES ( '";
    for( $i = 0; $i < 7; $i++ ){
      for( $j = 0; $j < 3; $j++ ){
        if( $j == 2 ){
            if( strpos( $_log[$this->week[$i]][$this->kind[$j]], '|' ) !== false ){
              $div = explode( '|', $_log[$this->week[$i]][$this->kind[$j]] );
              $size = count( $div );
              for( $k = 0; $k < $size; $k++ ){
                $query = $query.$tmp.$div[$k]."', '".$this->kind[$j]."', ( NOW() + INTERVAL ".$i." DAY ) );";
              }
              break;
            }
        }
        $query = $query.$tmp.$_log[$this->week[$i]][$this->kind[$j]]."', '".$this->kind[$j]."', ( NOW() + INTERVAL ".$i." DAY ) );";
      }
    }
    $this->_db_select( "Users_Geo" );
    $result = mysqli_multi_query($this->_connection, $query);
    if( !$result ){
      print( "Quely Failed.\n".mysqli_error( $this->_connection ) );
      return false;
    }
    do{
      mysqli_store_result( $this->_connection );
    } while( mysqli_next_result( $this->_connection ) );

    return true;
  }
  /* method that get menu log */
  public function GetMenuLog( $_uid ){
    $days = 0;
    $back_date = NULL;
    $back_kind = NULL;
    $return = array();
    $query = "SELECT * from UN".$_uid.";";

    $result = $this->_db_throw_query( "Users_Geo", $query );
    if( !$result ){
      print( "Quely Failed.\n".mysqli_error( $this->_connection ) );
      return NULL;
    }

    while( ( $record = mysqli_fetch_assoc( $result ) ) != NULL ){
      if( $back_date != NULL && strcmp( $record["Date"], $back_date ) != 0 )
        $days++;
      if( $record["Kind_Name"] == $back_kind )
        $return[$this->week[$days]][$back_kind] = $return[$this->week[$days]][$back_kind]."|".$record["Menu_Name"];
      else
        $return[$this->week[$days]][$record["Kind_Name"]] = $record["Menu_Name"];
      $back_date = $record["Date"];
      $back_kind = $record["Kind_Name"];
    }
    return $return;
  }
  /* end of public method */

  /* destructor */
  public function __destruct(){
    $this->_db_close();
  }
  /* end of destructor */
}
?>