<?php

	$modul_no = "0";

	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
	//~~~~~~~~~~~~~ Ýncler Yapýlýyor ~~~~~~~~~//
	//~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~//
	
	ob_start();
		
	include "login_check.php";
	include "../class/thumbnail.php";


$do 	= trim($_REQUEST['do']);
$q	 	= trim($_REQUEST['q']);
$type 	= trim($_REQUEST['type']);
$field  = trim($_REQUEST['field']);

if(!$field){
$field = "image";
}

// Resim Klasörü
$dir = $settings['root_path']."/images/".$type;

// Resim Gösterim Adresi 
$view_url = $settings['site_url']."/images/".$type."/";

// Ýzinli Resim Uzantýlarý
$allow_types = $settings['image_type'];

// Resim Boyutu KB
$image_size		= $settings['image_size'];

// Resim Uzantýsý Alma
function get_ext($key) { 
	$key=strtolower(substr(strrchr($key, "."), 1));
	$key=str_replace("jpeg","jpg",$key);
	return $key;
}

$ext_count=count($allow_types);
$i=0;
foreach($allow_types as $extension) {
	
	if($i <= $ext_count-2){
		$types .="*.".$extension.", ";
	}else{
		$types .="*.".$extension;
	}
	$i++;
}
unset($i,$ext_count); // why not


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Resim Yönetimi</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-9">
<link rel="stylesheet" href="images/style.css" type="text/css" />
<style>
BODY{
margin:1px;
background:#BCCED9;
}
.style1 {color: #FFFFFF}
.style2 {color: #000000}
</style>
<?php
	if($type == "other"){
?>
<script language="javascript">
	function imageAdd(_width, _height, image, filepath)
		{
			window.opener.document.getElementById('f_url').value = filepath;
			window.close();
		}
</script>
<?php
}else{
?>
<script language="javascript">
	function imageAdd(_width, _height, image, filepath)
		{
			window.opener.document.addForm.image.value = image;
			window.close();
		}
</script>
<?php
}
?>

</head>

<body>
<table width="100%" border="0" cellspacing="1" cellpadding="0" class="table_box" >
  <?php
  
  if($_POST['upload']){
  echo "<tr>\n";
  echo " <td colspan=\"2\" align=\"center\" class=\"style1 listEven row5px\" style=\"height:15px; background:url(images/popup-head.png)\" >";
  echo "<strong>Resim Yükleme</strong></td>\n";
  echo " </tr>\n";
  echo "<tr>\n";
  echo "  <td class=\"row5px listEven\" align=\"center\">\n";
  			
		$ext=get_ext($_FILES['file']['name']);
		$size=$_FILES['file']['size'];
		$image_size_byte=$image_size*1024;
		
			
			if(!in_array($ext, $allow_types)) {
							
			echo "Bu Dosya uzantýsýný yükleyemezsiniz : ".$_FILES['file']['name'].", sadece ".$types."
			uzantýlý dosyalarý yükleyebilirsiniz.<br />Resim <b>yüklenemedi </b><br /><br><a href=\"javascript:history.go(-1)\">Geri</a>";
			exit;
					
			}elseif($size > $image_size_byte) {
				
			echo "Yüklenecek resim: ".$_FILES['file']['name']." boyutu çok büyük.Max resim boyutu
			".$settings['image_file_size']." KB.<br />Resim <b>yüklenemedi</b><br /><br><a href=\"javascript:history.go(-1)\">Geri</a>";
			exit;	
			}else{
			
			$ext=get_ext($_FILES['file']['name']);
			
			$query = $db->read_query("select newname from images where type='$type' ORDER BY image_id DESC LIMIT 1") or die($db->sql_error());
			$row = $db->sql_fetcharray($query);
			$last_img = explode(".",$row[newname]);
			$new_image_name = ($last_img[0]+1).".".$ext;
							


			if(@move_uploaded_file($_FILES['file']['tmp_name'],$dir."/".$new_image_name)) {
				
				$db->write_query("insert into images
						(
						oldname, newname, type, size
						) 
						values
						(
						'".$_FILES['file']['name']."',
						'".$new_image_name."',
						'".$type."',
						'".$size."'
						)
						");

				$thumb = new Thumbnail($dir."/".$new_image_name);

				if($type == "news"){ // Haber Resmi Ýse
				
				$thumb->manual_resize($settings['news_image_width'],$settings['news_image_height']);
				$thumb->save($dir."/".$new_image_name,100);
				
				}elseif($type == "authors"){ // Yazar Resmi Ýse
				
				$thumb->manual_resize($settings['authors_big_width'],$settings['authors_big_height']);
				$thumb->save($dir."/".$new_image_name,100);
				
				$thumb_small = new Thumbnail($dir."/".$new_image_name);
				
				$thumb_small->manual_resize($settings['authors_small_width'],$settings['authors_small_height']);
				$thumb_small->save($dir."/th_".$new_image_name,100);
				
				}else{
				
				$image_pix = @getimagesize($dir."/".$new_image_name);
				$width_org = $image_pix[0];
				
					if($width_org > $settings['page_image_max_width']){
					
					$thumb->resize($settings['page_image_max_width']);
					$thumb->save($dir."/".$new_image_name,100);
					
					}
				
				
				} // Resim Tipi Kontrol Bitiþ
								
				@chmod($dir."/".$new_image_name,0644);
			
			}else{
			
			echo "Resim Yüklenemedi.Lütfen Sonra Tekrar Deneyiniz.";
			exit;
			
			} // Yükleme Bitiþ
		
		
		echo "<input type=\"hidden\" name=urlImage_".$new_imagename." id=urlImage_".$new_imagename." value=\"".$view_url.$new_image_name."\">\n";
		$image_file_size=number_format($_FILES['file']['size']/1024, 1, ".", "");	
		echo "<img src=\"".$view_url.$new_image_name."\" border=\"0\" id=img_upload><br>
		<a href=\"javascript:;\" onclick=\"javascript:imageAdd(document.all.img_upload.width, document.all.img_upload.height, '".$new_image_name."', '".$view_url.$new_image_name."'); window.close();\">Resmi Kullan [ Resim Adý : ".$_FILES['file']['name']." ] [ $image_file_size KB ]</a> [ <a href=\"".$_SERVER['PHP_SELF']."?do=delete&image_id=".$db->sql_nextid()."\">Sil</a> ] <p>\n";
		}
  
  echo "<input name=\"back\" type=\"button\" onclick=\"javascript:history.go(-1);\" class=\"button\" id=\"back\" value=\"Geri Dön\" />";
  echo "<input name=\"close\" type=\"button\" onclick=\"javascript:window.close()\" class=\"button\" id=\"close\" value=\"Vazgeç\" />";
  echo "  </td>\n";
  echo "</tr>\n";
  
}elseif($_POST['search_post']){

  echo "<tr>\n";
  echo " <td colspan=\"2\" align=\"center\" class=\"style1 listEven row5px\" style=\"height:15px; background:url(images/popup-head.png)\" >";
  echo "<strong>Arama Sonuçlarý</strong></td>\n";
  echo " </tr>\n";
  echo "<tr>\n";
  echo "  <td class=\"row5px listEven\" align=\"center\">\n";
  
  if(!$q){
 	echo "Lütfen Formu Doldurun..<br /><br><a href=\"javascript:history.go(-1)\">Geri</a>";
	exit;
  }elseif(strlen($q)<3){
 	echo "En Az 3 Karakter Girmelisiniz..<br /><br><a href=\"javascript:history.go(-1)\">Geri</a>";
	exit;
  }else{
    
  	$query = $db->read_query("select image_id, newname, oldname, size from images where type='$type' and oldname LIKE '%$q%'") or die($db->sql_error());
  	$i=0;
	while($row = $db->sql_fetcharray($query)){
	$i++;

	$image_file_size=number_format($row[size]/1024, 1, ".", "");	
	$img_url = $view_url.$row[newname];
	echo "<input type=\"hidden\" name=urlImage_".$row[newname]." id=urlImage_".$row[newname]." value=\"".$img_url."\">\n";
	echo "<img src=\"".$img_url."\" border=\"0\" id=img_upload><br>";
	echo "<a href=\"javascript:;\" 
	onclick=\"javascript:imageAdd(document.all.img_upload.width, document.all.img_upload.height, '".$row[newname]."', '".$img_url."'); window.close();\">Resmi Kullan [ Resim Adý : ".$row[oldname]." ] [ $image_file_size KB ]</a> [ <a href=\"".$_SERVER['PHP_SELF']."?do=delete&image_id=".$row[image_id]."\">Sil</a> ] <p>\n";
	}
	
	if(!$i){
	echo "Kayýt Bulunamadý..<br /><br><a href=\"javascript:history.go(-1)\">Geri</a>";
	exit;
	}
  }
  
  echo "<input name=\"back\" type=\"button\" onclick=\"javascript:history.go(-1);\" class=\"button\" id=\"back\" value=\"Geri Dön\" />";
  echo "<input name=\"close\" type=\"button\" onclick=\"javascript:window.close()\" class=\"button\" id=\"close\" value=\"Vazgeç\" />";
  echo "  </td>\n";
  echo "</tr>\n";
  
}elseif($do == "delete"){
	
	$image_id = intval($_GET['image_id']);
	$query = $db->read_query("select type, newname from images where image_id=$image_id") or die($db->sql_error());
	$row = $db->sql_fetcharray($query);
	
	if(file_exists($settings['root_path']."/images/".$row[type]."/".$row[newname])){
	@unlink($settings['root_path']."/images/".$row[type]."/".$row[newname]);
	}
	$query = $db->write_query("delete from images where image_id=$image_id") or die($db->sql_error());
	echo "<script>alert('Resim Silindi.'); window.location = 'image.php?do=upload&type=".$row[type]."';</script>";

}elseif($do == "search"){
?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="hidden" value="<?=$type?>" name="type" />
<input type="hidden" value="<?=$do?>" name="do" />
<input type="hidden" value="<?=$rte?>" name="rte" />
  <tr>
    <td colspan="2" align="center"  class="style1 listEven row5px" style="height:15px; background:url(images/popup-head.png)" ><strong>Arama Yapmak Ýstediðiniz Kriterleri Giriniz</strong></td>
  </tr>

  <tr>
    <td width="30%" class="row5px listEven"><b>Kelime Girin   :</b></td>
    <td width="70%" class="row5px listOdd"><input name="q" type="text" size="50" />
    En az 3 Karakter </td>
  </tr>
   <tr>
    <td width="30%" class="row5px listEven" align="center"> </td>
    <td width="70%" class="row5px listOdd"><input name="search_post" type="submit" class="button" id="search_post" value="Ara" />
      <input name="close23" type="button" onclick="javascript:window.close()" class="button" id="close23" value="Vazgeç" />
      <input name="uploadimg" type="button" onclick="javascript:window.location='<?=$_SERVER['PHP_SELF']?>?do=upload&rte=<?=$rte?>&type=<?=$type?>'" class="button" id="uploadimg" value="Resim Yükle" /></td>
  </tr>
  </form>
<?php
}else{ // Upload Formu
  ?>
<form action="<?=$_SERVER['PHP_SELF']?>" method="post" enctype="multipart/form-data">
<input type="hidden" value="<?=$type?>" name="type" />
<input type="hidden" value="<?=$do?>" name="do" />
<input type="hidden" value="<?=$rte?>" name="rte" />
  <tr>
    <td colspan="2" align="center"  class="style1 listEven row5px" style="height:15px; background:url(images/popup-head.png)" ><strong>Yüklenecek Resmi Seçiniz</strong></td>
  </tr>
<tr>
    <td colspan="2" align="left" class="row5px listEven style2" style="height:15px; line-height:140%; background: #A5BBC8" >Yüklemek Ýstediðiniz Resmi &quot; GÖZAT &quot; butonuna basarak seçiniz ve Yükle butonuna Basýnýz. <br />Sadece <?=$types?> uzantýlý resimleri yükleyebilirsiniz.</td>
  </tr>
  <tr>
    <td width="30%" class="row5px listEven"><b>Resim Seçin  :</b></td>
    <td width="70%" class="row5px listOdd"><input name="file" type="file" size="50" /></td>
  </tr>
   <tr>
    <td width="30%" class="row5px listEven" align="center"> </td>
    <td width="70%" class="row5px listOdd"><input name="upload" type="submit" class="button" id="upload" value="Yükle" />
      <input name="close222" type="button" onclick="javascript:window.close()" class="button" id="close222" value="Vazgeç" />
      <input name="imgsearch" type="button" onclick="javascript:window.location='<?=$_SERVER['PHP_SELF']?>?do=search&rte=<?=$rte?>&type=<?=$type?>'" class="button" id="imgsearch" value="Resim Ara" /></td>
  </tr>
  </form>
  <?php
  }
  ?>
</table>

</body>
</html>
