<?php

$as = array(
		"/:test"  => 1,
		);

require_once("process/boil1.php");
require_once("process/boil2.php");
require_once("process/boil3.php");
require_once("process/boil4.php");
require_once("process/burn1.php");
require_once("process/burn2.php");
require_once("process/burn3.php");
require_once("process/clean1.php");
require_once("process/cool1.php");
require_once("process/cut1.php");
require_once("process/cut2.php");
require_once("process/cut3.php");
require_once("process/cut4.php");
require_once("process/disable.php");
require_once("process/fry1.php");
require_once("process/measure1.php");
require_once("process/mix1.php");
require_once("process/mix2.php");
require_once("process/mix3.php");
require_once("process/saute1.php");
require_once("process/saute2.php");
require_once("process/saute3.php");
require_once("process/steam1.php");
require_once("recipe/r01.php");
require_once("recipe/r02.php");
require_once("recipe/r03.php");
require_once("recipe/r04.php");
require_once("recipe/r05.php");
require_once("recipe/r06.php");
require_once("recipe/r07.php");
require_once("recipe/r08.php");
require_once("recipe/r09.php");
require_once("recipe/r10.php");
require_once("recipe/r11.php");
require_once("recipe/r12.php");
require_once("recipe/r13.php");
require_once("recipe/r14.php");
require_once("recipe/r15.php");
require_once("recipe/r16.php");
require_once("recipe/r17.php");
require_once("recipe/r18.php");
require_once("recipe/r19.php");
require_once("recipe/r20.php");
require_once("recipe/r21.php");
require_once("recipe/r22.php");
require_once("recipe/r23.php");
require_once("recipe/r24.php");
require_once("recipe/r25.php");
require_once("recipe/r26.php");
require_once("recipe/r27.php");
require_once("recipe/r28.php");
require_once("recipe/r29.php");
require_once("recipe/r30.php");
require_once("recipe/r31.php");
require_once("recipe/r32.php");
require_once("recipe/r33.php");
require_once("recipe/r34.php");
require_once("recipe/r35.php");
require_once("recipe/r36.php");
require_once("recipe/r37.php");
require_once("recipe/y01.php");
require_once("recipe/y02.php");
require_once("recipe/y03.php");
require_once("recipe/y04.php");
require_once("recipe/y05.php");
require_once("recipe/y06.php");
require_once("recipe/y07.php");
require_once("recipe/y08.php");
require_once("recipe/y09.php");
require_once("recipe/y10.php");
require_once("recipe/y11.php");
require_once("recipe/y12.php");
require_once("recipe/y13.php");
require_once("recipe/y14.php");
require_once("recipe/y15.php");
require_once("recipe/y16.php");
require_once("recipe/y17.php");
require_once("recipe/y18.php");
require_once("recipe/y19.php");
require_once("recipe/y20.php");
require_once("recipe/y21.php");
require_once("recipe/y22.php");
require_once("recipe/y23.php");
require_once("recipe/y24.php");
require_once("recipe/y25.php");
require_once("recipe/y26.php");
require_once("recipe/y27.php");
require_once("recipe/y28.php");
require_once("recipe/y29.php");
require_once("recipe/y30.php");
require_once("recipe/y32.php");
require_once("recipe/y33.php");
require_once("recipe/y34.php");
require_once("recipe/y36.php");
require_once("recipe/y38.php");
require_once("recipe/y39.php");
require_once("recipe/y40.php");
require_once("recipe/s01.php");
require_once("recipe/s02.php");
require_once("recipe/s03.php");
require_once("recipe/s04.php");
require_once("recipe/s05.php");
require_once("recipe/s06.php");
require_once("recipe/s07.php");
require_once("recipe/s08.php");
require_once("recipe/s09.php");
require_once("recipe/s10.php");
require_once("recipe/s11.php");
require_once("recipe/s12.php");
require_once("recipe/s13.php");
require_once("recipe/s14.php");
require_once("recipe/s15.php");
require_once("recipe/s16.php");
require_once("recipe/s17.php");
require_once("recipe/s18.php");
require_once("recipe/s19.php");
require_once("recipe/s20.php");
require_once("recipe/s21.php");
require_once("recipe/s22.php");
require_once("recipe/s23.php");
require_once("recipe/s24.php");
require_once("recipe/s25.php");

switch($_GET["test"]) {
	case 1:
		$file = array(
		 "1" => $boil1,
		 "2" => $boil2,
		 "3" => $boil3,
		 "4" => $boil4,
		 "5" => $burn1,
		 "6" => $burn2,
		 "7" => $burn3,
		 "8" => $clean1,
		 "9" => $cool1,
		 "10" => $cut1,
		 "11" => $cut2,
		 "12" => $cut3,
		 "13" => $cut4,
		 "14" => $disabled,
		 "15" => $fry1,
		 "16" => $measure1,
		 "17" => $mix1,
		 "18" => $mix2,
		 "19" => $mix3,
		 "20" => $saute1,
		 "21" => $saute2,
		 "22" => $saute3,
		 "23" => $steam1,
		 "24" => $y01,
		 "25" => $y02,
		 "26" => $y03,
		 "27" => $y04,
		 "28" => $y05,
			);
	break;
	case 2:
		$file = array(
		 "1" => $y06,
		 "2" => $y07,
		 "3" => $y08,
		 "4" => $y09,
		 "5" => $y10,
		 "6" => $y11,
		 "7" => $y12,
		 "8" => $y13,
		 "9" => $y14,
		 "10" => $y15,
		 "11" => $y16,
		 "12" => $y17,
		 "13" => $y18,
		 "14" => $y19,
		 "15" => $y20,
		 "16" => $y21,
		 "17" => $y22,
		 "18" => $y23,
		 "19" => $y24,
		 "20" => $y25,
		 "21" => $y26,
		 "22" => $y27,
		 "23" => $y28,
		 "24" => $y29,
		 "25" => $y30,
		 "26" => $y32,
		 "27" => $y33,
		 "28" => $y34,
			);
	break;
	case 3:
		$file = array(
		 "1" => $y36,
		 "2" => $y38,
		 "3" => $y39,
		 "4" => $y40,
		 "5" => $r01,
		 "6" => $r02,
		 "7" => $r03,
		 "8" => $r04,
		 "9" => $r05,
		 "10" => $r06,
		 "11" => $r07,
		 "12" => $r08,
		 "13" => $r09,
		 "14" => $r10,
		 "15" => $r11,
		 "16" => $r12,
		 "17" => $r13,
		 "18" => $r14,
		 "19" => $r15,
		 "20" => $r16,
		 "21" => $r17,
		 "22" => $r18,
		 "23" => $r19,
		 "24" => $r20,
		 "25" => $r21,
		 "26" => $r22,
		 "27" => $r23,
		 "28" => $r24,
			);
	break;
	case 4:
		$file = array(
		"1" => $r25,
		"2" => $r26,
		"3" => $r27,
		"4" => $r28,
		"5" => $r29,
		"6" => $r30,
		"7" => $r31,
		"8" => $r32,
		"9" => $r33,
		"10" => $r34,
		"11" => $r35,
		"12" => $r36,
		"13" => $r37,
		"14" => $s01,
		"15" => $s02,
		"16" => $s03,
		"17" => $s04,
		"18" => $s05,
		"19" => $s06,
		"20" => $s07,
		"21" => $s08,
		"22" => $s09,
		"23" => $s10,
		"24" => $s11,
		"25" => $s12,
		"26" => $s13,
		"27" => $s14,
		"28" => $s15,
			);
	break;
	case 5:
		$file = array(
		"1" => $s16,
		"2" => $s17,
		"3" => $s18,
		"4" => $s19,
		"5" => $s20,
		"6" => $s21,
		"7" => $s22,
		"8" => $s23,
		"9" => $s24,
		"10" => $s25,
			);
	break;
	default:
		$file = array(
		 "1" => $boil1,
		 "2" => $boil2,
		 "3" => $boil3,
		 "4" => $boil4,
		 "5" => $burn1,
		 "6" => $burn2,
		 "7" => $burn3,
		 "8" => $clean1,
		 "9" => $cool1,
		 "10" => $cut1,
		 "11" => $cut2,
		 "12" => $cut3,
		 "13" => $cut4,
		 "14" => $disable,
		 "15" => $fry1,
		 "16" => $measure1,
		 "17" => $mix1,
		 "18" => $mix2,
		 "19" => $mix3,
		 "20" => $saute1,
		 "21" => $saute2,
		 "22" => $saute3,
		 "23" => $steam1,
		 "24" => $y01,
		 "25" => $y02,
		 "26" => $y03,
		 "27" => $y04,
		 "28" => $y05,
			);
	break;
}

$head = "\x46\x57\x53\x04\x61\x0D\x00\x00\x80\x00\x02\x71\x00\x00\x00\x64\x00\x00\x0C\x01\x00\x43\x02\xFF\xFF\xFF";

$image_1 = !is_null($file[1]) ? "\xBF\x05".$file[1][0]."\x00\x00\x01\x00".$file[1][1]."\xFF\x09\x10\x00\x00\x00\x02\x00\x01\x00\x86\x06\x06\x01\x00\x01\x00\x00\x40\x00\x00\x00\xBF\x06\x0D\x00\x00\x00\x26\x01\x00\x02\x00\x12\xF0\x78\x00\x6D\x63\x31\x00" : null;

$image_2 = !is_null($file[2]) ? "\xBF\x05".$file[2][0]."\x00\x00\x03\x00".$file[2][1]."\xFF\x09\x10\x00\x00\x00\x04\x00\x01\x00\x86\x06\x06\x01\x00\x03\x00\x00\x40\x00\x00\x00\xBF\x06\x12\x00\x00\x00\x26\x03\x00\x04\x00\xC9\x00\x0F\x40\x00\x0C\x66\x80\xF0\x6D\x63\x32\x00" : null;

$image_3 = !is_null($file[3]) ? "\xBF\x05".$file[3][0]."\x00\x00\x05\x00".$file[3][1]."\xFF\x09\x10\x00\x00\x00\x06\x00\x01\x00\x86\x06\x06\x01\x00\x05\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x05\x00\x06\x00\xC8\xFF\xF8\x40\x00\x0D\x5F\x00\x3C\x00\x6D\x63\x33\x00" : null;

$image_4 = !is_null($file[4]) ? "\xBF\x05".$file[4][0]."\x00\x00\x07\x00".$file[4][1]."\xFF\x09\x10\x00\x00\x00\x08\x00\x01\x00\x86\x06\x06\x01\x00\x07\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x07\x00\x08\x00\xC8\xFF\xF8\x40\x00\x0E\x45\x60\x0F\x00\x6D\x63\x34\x00" : null;

$image_5 = !is_null($file[5]) ? "\xBF\x05".$file[5][0]."\x00\x00\x09\x00".$file[5][1]."\xFF\x09\x10\x00\x00\x00\x0A\x00\x01\x00\x86\x06\x06\x01\x00\x09\x00\x00\x40\x00\x00\x00\xBF\x06\x0E\x00\x00\x00\x26\x09\x00\x0A\x00\x1C\xB6\x80\x1E\x00\x6D\x63\x35\x00" : null;

$image_6 = !is_null($file[6]) ? "\xBF\x05".$file[6][0]."\x00\x00\x0B\x00".$file[6][1]."\xFF\x09\x10\x00\x00\x00\x0C\x00\x01\x00\x86\x06\x06\x01\x00\x0B\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x0B\x00\x0C\x00\xC9\x00\x0F\x40\x00\x0E\x71\x20\x0F\x00\x6D\x63\x36\x00" : null;

$image_7 = !is_null($file[7]) ? "\xBF\x05".$file[7][0]."\x00\x00\x0D\x00".$file[7][1]."\xFF\x09\x10\x00\x00\x00\x0E\x00\x01\x00\x86\x06\x06\x01\x00\x0D\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x0D\x00\x0E\x00\xC8\xFF\xF8\x40\x00\x0F\x43\x80\x03\xC0\x6D\x63\x37\x00" : null;

$image_8 = !is_null($file[8]) ? "\xBF\x05".$file[8][0]."\x00\x00\x0F\x00".$file[8][1]."\xFF\x09\x10\x00\x00\x00\x10\x00\x01\x00\x86\x06\x06\x01\x00\x0F\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x0F\x00\x10\x00\xC8\xFF\xF8\x40\x00\x0F\x4E\x70\x03\xC0\x6D\x63\x38\x00" : null;

$image_9 = !is_null($file[9]) ? "\xBF\x05".$file[9][0]."\x00\x00\x11\x00".$file[9][1]."\xFF\x09\x10\x00\x00\x00\x12\x00\x01\x00\x86\x06\x06\x01\x00\x11\x00\x00\x40\x00\x00\x00\xBF\x06\x0E\x00\x00\x00\x26\x11\x00\x12\x00\x1E\xB2\xC0\x07\x80\x6D\x63\x39\x00" : null;

$image_10 = !is_null($file[10]) ? "\xBF\x05".$file[10][0]."\x00\x00\x13\x00".$file[10][1]."\xFF\x09\x10\x00\x00\x00\x14\x00\x01\x00\x86\x06\x06\x01\x00\x13\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x13\x00\x14\x00\xC9\x00\x0F\x40\x00\x0F\x64\x50\x03\xC0\x6D\x63\x31\x30\x00" : null;

$image_11 = !is_null($file[11]) ? "\xBF\x05".$file[11][0]."\x00\x00\x15\x00".$file[11][1]."\xFF\x09\x10\x00\x00\x00\x16\x00\x01\x00\x86\x06\x06\x01\x00\x15\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x15\x00\x16\x00\xC8\xFF\xF8\x40\x00\x0F\x6F\x40\x03\xC0\x6D\x63\x31\x31\x00" : null;

$image_12 = !is_null($file[12]) ? "\xBF\x05".$file[12][0]."\x00\x00\x17\x00".$file[12][1]."\xFF\x09\x10\x00\x00\x00\x18\x00\x01\x00\x86\x06\x06\x01\x00\x17\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x17\x00\x18\x00\xC8\xFF\xF8\x40\x00\x0F\x7A\x30\x03\xC0\x6D\x63\x31\x32\x00" : null;

$image_13 = !is_null($file[13]) ? "\xBF\x05".$file[13][0]."\x00\x00\x19\x00".$file[13][1]."\xFF\x09\x10\x00\x00\x00\x1A\x00\x01\x00\x86\x06\x06\x01\x00\x19\x00\x00\x40\x00\x00\x00\xBF\x06\x0F\x00\x00\x00\x26\x19\x00\x1A\x00\x20\x85\x20\x01\xE0\x6D\x63\x31\x33\x00" : null;

$image_14 = !is_null($file[14]) ? "\xBF\x05".$file[14][0]."\x00\x00\x1B\x00".$file[14][1]."\xFF\x09\x10\x00\x00\x00\x1C\x00\x01\x00\x86\x06\x06\x01\x00\x1B\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x1B\x00\x1C\x00\xC9\x00\x0F\x40\x00\x10\x48\x08\x00\xF0\x6D\x63\x31\x34\x00" : null;

$image_15 = !is_null($file[15]) ? "\xBF\x05".$file[15][0]."\x00\x00\x1D\x00".$file[15][1]."\xFF\x09\x10\x00\x00\x00\x1E\x00\x01\x00\x86\x06\x06\x01\x00\x1D\x00\x00\x40\x00\x00\x00\xBF\x06\x0E\x00\x00\x00\x26\x1D\x00\x1E\x00\x18\x1E\x0C\xD0\x6D\x63\x31\x35\x00" : null;

$image_16 = !is_null($file[16]) ? "\xBF\x05".$file[16][0]."\x00\x00\x1F\x00".$file[16][1]."\xFF\x09\x10\x00\x00\x00\x20\x00\x01\x00\x86\x06\x06\x01\x00\x1F\x00\x00\x40\x00\x00\x00\xBF\x06\x13\x00\x00\x00\x26\x1F\x00\x20\x00\xC9\x00\x0F\x40\x00\x0C\x66\x86\x68\x6D\x63\x31\x36\x00" : null;

$image_17 = !is_null($file[17]) ? "\xBF\x05".$file[17][0]."\x00\x00\x21\x00".$file[17][1]."\xFF\x09\x10\x00\x00\x00\x22\x00\x01\x00\x86\x06\x06\x01\x00\x21\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x21\x00\x22\x00\xC8\xFF\xF8\x40\x00\x0D\x5F\x01\x9A\x00\x6D\x63\x31\x37\x00" : null;

$image_18 = !is_null($file[18]) ? "\xBF\x05".$file[18][0]."\x00\x00\x23\x00".$file[18][1]."\xFF\x09\x10\x00\x00\x00\x24\x00\x01\x00\x86\x06\x06\x01\x00\x23\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x23\x00\x24\x00\xC8\xFF\xF8\x40\x00\x0E\x45\x60\x66\x80\x6D\x63\x31\x38\x00" : null;

$image_19 = !is_null($file[19]) ? "\xBF\x05".$file[19][0]."\x00\x00\x25\x00".$file[19][1]."\xFF\x09\x10\x00\x00\x00\x26\x00\x01\x00\x86\x06\x06\x01\x00\x25\x00\x00\x40\x00\x00\x00\xBF\x06\x0F\x00\x00\x00\x26\x25\x00\x26\x00\x1C\xB6\x80\xCD\x00\x6D\x63\x31\x39\x00" : null;

$image_20 = !is_null($file[20]) ? "\xBF\x05".$file[20][0]."\x00\x00\x27\x00".$file[20][1]."\xFF\x09\x10\x00\x00\x00\x28\x00\x01\x00\x86\x06\x06\x01\x00\x27\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x27\x00\x28\x00\xC9\x00\x0F\x40\x00\x0E\x71\x20\x66\x80\x6D\x63\x32\x30\x00" : null;

$image_21 = !is_null($file[21]) ? "\xBF\x05".$file[21][0]."\x00\x00\x29\x00".$file[21][1]."\xFF\x09\x10\x00\x00\x00\x2A\x00\x01\x00\x86\x06\x06\x01\x00\x29\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x29\x00\x2A\x00\xC8\xFF\xF8\x40\x00\x0F\x43\x80\x19\xA0\x6D\x63\x32\x31\x00" : null;

$image_22 = !is_null($file[22]) ? "\xBF\x05".$file[22][0]."\x00\x00\x2B\x00".$file[22][1]."\xFF\x09\x10\x00\x00\x00\x2C\x00\x01\x00\x86\x06\x06\x01\x00\x2B\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x2B\x00\x2C\x00\xC8\xFF\xF8\x40\x00\x0F\x4E\x70\x19\xA0\x6D\x63\x32\x32\x00" : null;

$image_23 = !is_null($file[23]) ? "\xBF\x05".$file[23][0]."\x00\x00\x2D\x00".$file[23][1]."\xFF\x09\x10\x00\x00\x00\x2E\x00\x01\x00\x86\x06\x06\x01\x00\x2D\x00\x00\x40\x00\x00\x00\xBF\x06\x0F\x00\x00\x00\x26\x2D\x00\x2E\x00\x1E\xB2\xC0\x33\x40\x6D\x63\x32\x33\x00" : null;

$image_24 = !is_null($file[24]) ? "\xBF\x05".$file[24][0]."\x00\x00\x2F\x00".$file[24][1]."\xFF\x09\x10\x00\x00\x00\x30\x00\x01\x00\x86\x06\x06\x01\x00\x2F\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x2F\x00\x30\x00\xC9\x00\x0F\x40\x00\x0F\x64\x50\x19\xA0\x6D\x63\x32\x34\x00" : null;

$image_25 = !is_null($file[25]) ? "\xBF\x05".$file[25][0]."\x00\x00\x31\x00".$file[25][1]."\xFF\x09\x10\x00\x00\x00\x32\x00\x01\x00\x86\x06\x06\x01\x00\x31\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x31\x00\x32\x00\xC8\xFF\xF8\x40\x00\x0F\x6F\x40\x19\xA0\x6D\x63\x32\x35\x00" : null;

$image_26 = !is_null($file[26]) ? "\xBF\x05".$file[26][0]."\x00\x00\x33\x00".$file[26][1]."\xFF\x09\x10\x00\x00\x00\x34\x00\x01\x00\x86\x06\x06\x01\x00\x33\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x33\x00\x34\x00\xC8\xFF\xF8\x40\x00\x0F\x7A\x30\x19\xA0\x6D\x63\x32\x36\x00" : null;

$image_27 = !is_null($file[27]) ? "\xBF\x05".$file[27][0]."\x00\x00\x35\x00".$file[27][1]."\xFF\x09\x10\x00\x00\x00\x36\x00\x01\x00\x86\x06\x06\x01\x00\x35\x00\x00\x40\x00\x00\x00\xBF\x06\x0F\x00\x00\x00\x26\x35\x00\x36\x00\x20\x85\x20\x0C\xD0\x6D\x63\x32\x37\x00" : null;

$image_28 = !is_null($file[28]) ? "\xBF\x05".$file[28][0]."\x00\x00\x37\x00".$file[28][1]."\xFF\x09\x10\x00\x00\x00\x38\x00\x01\x00\x86\x06\x06\x01\x00\x37\x00\x00\x40\x00\x00\x00\xBF\x06\x14\x00\x00\x00\x26\x37\x00\x38\x00\xC9\x00\x0F\x40\x00\x10\x48\x08\x06\x68\x6D\x63\x32\x38\x00" : null;

$foot = "\x40\x00\x00\x00";

$source = $head . $image_1 . $image_2 . $image_3 . $image_4 . $image_5 . $image_6 . $image_7 . $image_8 . $image_9 . $image_10 . $image_11 . $image_12 . $image_13 . $image_14 . $image_15 . $image_16 . $image_17 . $image_18 . $image_19 . $image_20 . $image_21 . $image_22 . $image_23 . $image_24 . $image_25 . $image_26 . $image_27 . $image_28 . $foot;

header("Content-Type: application/x-shockwave-flash");

echo swf_wrapper($source,$as);

function swf_wrapper($src,$item){
	$tags	= build_tags($item);
	$i	= (ord($src[8])>>1)+5;
	$length	= ceil((((8-($i&7))&7)+$i)/8)+17;
	$head	= substr($src,0,$length);
	return(
		substr($head,0,4).
		pack("V",strlen($src)+strlen($tags)).
		substr($head,8).
		$tags.
		substr($src,$length)
	);
}

function build_tags($item){
	$tags = array();
	foreach($item as $k => $v){
		$v = mb_convert_encoding($v,'SJIS','UTF-8');
		array_push( $tags, sprintf(
			"\x96%s\x00%s\x00\x96%s\x00%s\x00\x1d",
			pack("v",strlen($k)+2),	$k,
			pack("v",strlen($v)+2),	$v
		));
	}
	$s = implode('',$tags);
	return(sprintf(
		"\x3f\x03%s%s\x00",
		pack("V",strlen($s)+1),
		$s
	));
}

?>