<?php namespace utils;

use DateTime;
use DateTimeZone;

/**
 * CountryTimeZones.php
 * @see https://github.com/jeffreymorganio/country-geolocation-data/blob/master/CountryTimeZones.php
 */
class TimeZone
{
	
	
	/*
	* Map a two-letter country code onto the name of the country's time zone.
	* Countries with multiple time-zones are represented by an array of time-zone
	* name and time-zone longitude pairs.
	*/
	public static $COUNTRY_TIME_ZONES = array(
		"AD" => "Europe/Andorra",
		"AE" => "Asia/Dubai",
		"AF" => "Asia/Kabul",
		"AG" => "America/Antigua",
		"AI" => "America/Anguilla",
		"AL" => "Europe/Tirane",
		"AM" => "Asia/Yerevan",
		"AN" => "America/Curacao",
		"AO" => "Africa/Luanda",
		"AQ" => array(
			array("Antarctica/Casey", 110.516667),
			array("Antarctica/Davis", 77.966667),
			array("Antarctica/DumontDUrville", 140.016667),
			array("Antarctica/Mawson", 62.883333),
			array("Antarctica/McMurdo", 166.6),
			array("Antarctica/Palmer", -64.1),
			array("Antarctica/Rothera", -68.133333),
			array("Antarctica/South_Pole", 0),
			array("Antarctica/Syowa", 39.59),
			array("Antarctica/Vostok", 106.9)
		),
		"AR" => array(
			array("America/Argentina/Buenos_Aires", -58.45),
			array("America/Argentina/Catamarca", -65.783333),
			array("America/Argentina/Cordoba", -64.183333),
			array("America/Argentina/Jujuy", -65.3),
			array("America/Argentina/La_Rioja", -66.85),
			array("America/Argentina/Mendoza", -68.816667),
			array("America/Argentina/Rio_Gallegos", -69.216667),
			array("America/Argentina/San_Juan", -68.516667),
			array("America/Argentina/Tucuman", -65.216667),
			array("America/Argentina/Ushuaia", -68.3)
		),
		"AS" => "Pacific/Pago_Pago",
		"AT" => "Europe/Vienna",
		"AU" => array(
			array("Australia/Adelaide", 138.583333),
			array("Australia/Brisbane", 153.033333),
			array("Australia/Broken_Hill", 141.45),
			array("Australia/Currie", 143.866667),
			array("Australia/Darwin", 130.833333),
			array("Australia/Eucla", 128.866667),
			array("Australia/Hobart", 147.316667),
			array("Australia/Lindeman", 149),
			array("Australia/Lord_Howe", 159.083333),
			array("Australia/Melbourne", 144.966667),
			array("Australia/Perth", 115.85),
			array("Australia/Sydney", 151.216667)
		),
		"AW" => "America/Aruba",
		"AX" => "Europe/Mariehamn",
		"AZ" => "Asia/Baku",
		"BA" => "Europe/Sarajevo",
		"BB" => "America/Barbados",
		"BD" => "Asia/Dhaka",
		"BE" => "Europe/Brussels",
		"BF" => "Africa/Ouagadougou",
		"BG" => "Europe/Sofia",
		"BH" => "Asia/Bahrain",
		"BI" => "Africa/Bujumbura",
		"BJ" => "Africa/Porto-Novo",
		"BM" => "Atlantic/Bermuda",
		"BN" => "Asia/Brunei",
		"BO" => "America/La_Paz",
		"BR" => array(
			array("America/Araguaina", -48.2),
			array("America/Bahia", -38.516667),
			array("America/Belem", -48.483333),
			array("America/Boa_Vista", -60.666667),
			array("America/Campo_Grande", -54.616667),
			array("America/Cuiaba", -56.083333),
			array("America/Eirunepe", -69.866667),
			array("America/Fortaleza", -38.5),
			array("America/Maceio", -35.716667),
			array("America/Manaus", -60.016667),
			array("America/Noronha", -32.416667),
			array("America/Porto_Velho", -63.9),
			array("America/Recife", -34.9),
			array("America/Rio_Branco", -67.8),
			array("America/Sao_Paulo", -46.616667)
		),
		"BS" => "America/Nassau",
		"BT" => "Asia/Thimphu",
		"BW" => "Africa/Gaborone",
		"BY" => "Europe/Minsk",
		"BZ" => "America/Belize",
		"CA" => array(
			array("America/Atikokan", -91.621667),
			array("America/Blanc-Sablon", -57.116667),
			array("America/Cambridge_Bay", -105.052778),
			array("America/Dawson", -139.416667),
			array("America/Dawson_Creek", -120.233333),
			array("America/Edmonton", -113.466667),
			array("America/Glace_Bay", -59.95),
			array("America/Goose_Bay", -60.416667),
			array("America/Halifax", -63.6),
			array("America/Inuvik", -133.716667),
			array("America/Iqaluit", -68.466667),
			array("America/Moncton", -64.783333),
			array("America/Montreal", -73.566667),
			array("America/Nipigon", -88.266667),
			array("America/Pangnirtung", -65.733333),
			array("America/Rainy_River", -94.566667),
			array("America/Rankin_Inlet", -92.083056),
			array("America/Regina", -104.65),
			array("America/Resolute", -94.829167),
			array("America/St_Johns", -52.716667),
			array("America/Swift_Current", -107.833333),
			array("America/Thunder_Bay", -89.25),
			array("America/Toronto", -79.383333),
			array("America/Vancouver", -123.116667),
			array("America/Whitehorse", -135.05),
			array("America/Winnipeg", -97.15),
			array("America/Yellowknife", -114.35)
		),
		"CC" => "Indian/Cocos",
		"CD" => array(
			array("Africa/Kinshasa", 15.3),
			array("Africa/Lubumbashi", 27.466667)
		),
		"CF" => "Africa/Bangui",
		"CG" => "Africa/Brazzaville",
		"CH" => "Europe/Zurich",
		"CI" => "Africa/Abidjan",
		"CK" => "Pacific/Rarotonga",
		"CL" => array(
			array("America/Santiago", -70.666667),
			array("Pacific/Easter", -109.433333)
		),
		"CM" => "Africa/Douala",
		"CN" => array(
			array("Asia/Chongqing", 106.583333),
			array("Asia/Harbin", 126.683333),
			array("Asia/Kashgar", 75.983333),
			array("Asia/Shanghai", 121.466667),
			array("Asia/Urumqi", 87.583333)
		),
		"CO" => "America/Bogota",
		"CR" => "America/Costa_Rica",
		"CU" => "America/Havana",
		"CV" => "Atlantic/Cape_Verde",
		"CX" => "Indian/Christmas",
		"CY" => "Asia/Nicosia",
		"CZ" => "Europe/Prague",
		"DE" => "Europe/Berlin",
		"DJ" => "Africa/Djibouti",
		"DK" => "Europe/Copenhagen",
		"DM" => "America/Dominica",
		"DO" => "America/Santo_Domingo",
		"DZ" => "Africa/Algiers",
		"EC" => array(
			array("America/Guayaquil", -79.833333),
			array("Pacific/Galapagos", -89.6)
		),
		"EE" => "Europe/Tallinn",
		"EG" => "Africa/Cairo",
		"EH" => "Africa/El_Aaiun",
		"ER" => "Africa/Asmara",
		"ES" => array(
			array("Africa/Ceuta", -5.316667),
			array("Atlantic/Canary", -15.4),
			array("Europe/Madrid", -3.683333)
		),
		"ET" => "Africa/Addis_Ababa",
		"FI" => "Europe/Helsinki",
		"FJ" => "Pacific/Fiji",
		"FK" => "Atlantic/Stanley",
		"FM" => array(
			array("Pacific/Kosrae", 162.983333),
			array("Pacific/Ponape", 158.216667),
			array("Pacific/Truk", 151.783333)
		),
		"FO" => "Atlantic/Faroe",
		"FR" => "Europe/Paris",
		"GA" => "Africa/Libreville",
		"GB" => "Europe/London",
		"GD" => "America/Grenada",
		"GE" => "Asia/Tbilisi",
		"GF" => "America/Cayenne",
		"GG" => "Europe/Guernsey",
		"GH" => "Africa/Accra",
		"GI" => "Europe/Gibraltar",
		"GL" => array(
			array("America/Danmarkshavn", -18.666667),
			array("America/Godthab", -51.733333),
			array("America/Scoresbysund", -21.966667),
			array("America/Thule", -68.783333)
		),
		"GM" => "Africa/Banjul",
		"GN" => "Africa/Conakry",
		"GP" => "America/Guadeloupe",
		"GQ" => "Africa/Malabo",
		"GR" => "Europe/Athens",
		"GS" => "Atlantic/South_Georgia",
		"GT" => "America/Guatemala",
		"GU" => "Pacific/Guam",
		"GW" => "Africa/Bissau",
		"GY" => "America/Guyana",
		"HK" => "Asia/Hong_Kong",
		"HN" => "America/Tegucigalpa",
		"HR" => "Europe/Zagreb",
		"HT" => "America/Port-au-Prince",
		"HU" => "Europe/Budapest",
		"ID" => array(
			array("Asia/Jakarta", 106.8),
			array("Asia/Jayapura", 140.7),
			array("Asia/Makassar", 119.4),
			array("Asia/Pontianak", 109.333333)
		),
		"IE" => "Europe/Dublin",
		"IL" => "Asia/Jerusalem",
		"IM" => "Europe/Isle_of_Man",
		"IN" => "Asia/Calcutta",
		"IO" => "Indian/Chagos",
		"IQ" => "Asia/Baghdad",
		"IR" => "Asia/Tehran",
		"IS" => "Atlantic/Reykjavik",
		"IT" => "Europe/Rome",
		"JE" => "Europe/Jersey",
		"JM" => "America/Jamaica",
		"JO" => "Asia/Amman",
		"JP" => "Asia/Tokyo",
		"KE" => "Africa/Nairobi",
		"KG" => "Asia/Bishkek",
		"KH" => "Asia/Phnom_Penh",
		"KI" => array(
			array("Pacific/Enderbury", -171.083333),
			array("Pacific/Kiritimati", -157.333333),
			array("Pacific/Tarawa", 173)
		),
		"KM" => "Indian/Comoro",
		"KN" => "America/St_Kitts",
		"KP" => "Asia/Pyongyang",
		"KR" => "Asia/Seoul",
		"KW" => "Asia/Kuwait",
		"KY" => "America/Cayman",
		"KZ" => array(
			array("Asia/Almaty", 76.95),
			array("Asia/Aqtau", 50.266667),
			array("Asia/Aqtobe", 57.166667),
			array("Asia/Oral", 51.35),
			array("Asia/Qyzylorda", 65.466667)
		),
		"LA" => "Asia/Vientiane",
		"LB" => "Asia/Beirut",
		"LC" => "America/St_Lucia",
		"LI" => "Europe/Vaduz",
		"LK" => "Asia/Colombo",
		"LR" => "Africa/Monrovia",
		"LS" => "Africa/Maseru",
		"LT" => "Europe/Vilnius",
		"LU" => "Europe/Luxembourg",
		"LV" => "Europe/Riga",
		"LY" => "Africa/Tripoli",
		"MA" => "Africa/Casablanca",
		"MC" => "Europe/Monaco",
		"MD" => "Europe/Chisinau",
		"ME" => "Europe/Podgorica",
		"MG" => "Indian/Antananarivo",
		"MH" => array(
			array("Pacific/Kwajalein", 167.333333),
			array("Pacific/Majuro", 171.2)
		),
		"MK" => "Europe/Skopje",
		"ML" => "Africa/Bamako",
		"MM" => "Asia/Rangoon",
		"MN" => array(
			array("Asia/Choibalsan", 114.5),
			array("Asia/Hovd", 91.65),
			array("Asia/Ulaanbaatar", 106.883333)
		),
		"MO" => "Asia/Macau",
		"MP" => "Pacific/Saipan",
		"MQ" => "America/Martinique",
		"MR" => "Africa/Nouakchott",
		"MS" => "America/Montserrat",
		"MT" => "Europe/Malta",
		"MU" => "Indian/Mauritius",
		"MV" => "Indian/Maldives",
		"MW" => "Africa/Blantyre",
		"MX" => array(
			array("America/Cancun", -86.766667),
			array("America/Chihuahua", -106.083333),
			array("America/Hermosillo", -110.966667),
			array("America/Mazatlan", -106.416667),
			array("America/Merida", -89.616667),
			array("America/Mexico_City", -99.15),
			array("America/Monterrey", -100.316667),
			array("America/Tijuana", -117.016667)
		),
		"MY" => array(
			array("Asia/Kuala_Lumpur", 101.7),
			array("Asia/Kuching", 110.333333)
		),
		"MZ" => "Africa/Maputo",
		"NA" => "Africa/Windhoek",
		"NC" => "Pacific/Noumea",
		"NE" => "Africa/Niamey",
		"NF" => "Pacific/Norfolk",
		"NG" => "Africa/Lagos",
		"NI" => "America/Managua",
		"NL" => "Europe/Amsterdam",
		"NO" => "Europe/Oslo",
		"NP" => "Asia/Katmandu",
		"NR" => "Pacific/Nauru",
		"NU" => "Pacific/Niue",
		"NZ" => array(
			array("Pacific/Auckland", 174.766667),
			array("Pacific/Chatham", -176.55)
		),
		"OM" => "Asia/Muscat",
		"PA" => "America/Panama",
		"PE" => "America/Lima",
		"PF" => array(
			array("Pacific/Gambier", -134.95),
			array("Pacific/Marquesas", -139.5),
			array("Pacific/Tahiti", -149.566667)
		),
		"PG" => "Pacific/Port_Moresby",
		"PH" => "Asia/Manila",
		"PK" => "Asia/Karachi",
		"PL" => "Europe/Warsaw",
		"PM" => "America/Miquelon",
		"PN" => "Pacific/Pitcairn",
		"PR" => "America/Puerto_Rico",
		"PS" => "Asia/Gaza",
		"PT" => array(
			array("Atlantic/Azores", -25.666667),
			array("Atlantic/Madeira", -16.9),
			array("Europe/Lisbon", -9.133333)
		),
		"PW" => "Pacific/Palau",
		"PY" => "America/Asuncion",
		"QA" => "Asia/Qatar",
		"RE" => "Indian/Reunion",
		"RO" => "Europe/Bucharest",
		"RS" => "Europe/Belgrade",
		"RU" => array(
			array("Asia/Anadyr", 177.483333),
			array("Asia/Irkutsk", 104.333333),
			array("Asia/Kamchatka", 158.65),
			array("Asia/Krasnoyarsk", 92.833333),
			array("Asia/Magadan", 150.8),
			array("Asia/Novosibirsk", 82.916667),
			array("Asia/Omsk", 73.4),
			array("Asia/Sakhalin", 142.7),
			array("Asia/Vladivostok", 131.933333),
			array("Asia/Yakutsk", 129.666667),
			array("Asia/Yekaterinburg", 60.6),
			array("Europe/Kaliningrad", 20.5),
			array("Europe/Moscow", 37.583333),
			array("Europe/Samara", 50.15),
			array("Europe/Volgograd", 44.416667)
		),
		"RW" => "Africa/Kigali",
		"SA" => "Asia/Riyadh",
		"SB" => "Pacific/Guadalcanal",
		"SC" => "Indian/Mahe",
		"SD" => "Africa/Khartoum",
		"SE" => "Europe/Stockholm",
		"SG" => "Asia/Singapore",
		"SH" => "Atlantic/St_Helena",
		"SI" => "Europe/Ljubljana",
		"SJ" => array(
			array("Arctic/Longyearbyen", 16),
			array("Atlantic/Jan_Mayen", -8.083333)
		),
		"SK" => "Europe/Bratislava",
		"SL" => "Africa/Freetown",
		"SM" => "Europe/San_Marino",
		"SN" => "Africa/Dakar",
		"SO" => "Africa/Mogadishu",
		"SR" => "America/Paramaribo",
		"ST" => "Africa/Sao_Tome",
		"SV" => "America/El_Salvador",
		"SY" => "Asia/Damascus",
		"SZ" => "Africa/Mbabane",
		"TC" => "America/Grand_Turk",
		"TD" => "Africa/Ndjamena",
		"TF" => "Indian/Kerguelen",
		"TG" => "Africa/Lome",
		"TH" => "Asia/Bangkok",
		"TJ" => "Asia/Dushanbe",
		"TK" => "Pacific/Fakaofo",
		"TL" => "Asia/Dili",
		"TM" => "Asia/Ashgabat",
		"TN" => "Africa/Tunis",
		"TO" => "Pacific/Tongatapu",
		"TR" => "Europe/Istanbul",
		"TT" => "America/Port_of_Spain",
		"TV" => "Pacific/Funafuti",
		"TW" => "Asia/Taipei",
		"TZ" => "Africa/Dar_es_Salaam",
		"UA" => array(
			array("Europe/Kiev", 30.516667),
			array("Europe/Simferopol", 34.1),
			array("Europe/Uzhgorod", 22.3),
			array("Europe/Zaporozhye", 35.166667)
		),
		"UG" => "Africa/Kampala",
		"UM" => array(
			array("Pacific/Johnston", -169.516667),
			array("Pacific/Midway", -177.366667),
			array("Pacific/Wake", 166.616667)
		),
		"US" => array(
			array("America/Adak", -176.658056),
			array("America/Anchorage", -149.900278),
			array("America/Boise", -116.2025),
			array("America/Chicago", -87.65),
			array("America/Denver", -104.984167),
			array("America/Detroit", -83.045833),
			array("America/Indiana/Indianapolis", -86.158056),
			array("America/Indiana/Knox", -86.625),
			array("America/Indiana/Marengo", -86.344722),
			array("America/Indiana/Petersburg", -87.278611),
			array("America/Indiana/Tell_City", -86.761389),
			array("America/Indiana/Vevay", -85.067222),
			array("America/Indiana/Vincennes", -87.528611),
			array("America/Indiana/Winamac", -86.603056),
			array("America/Juneau", -134.419722),
			array("America/Kentucky/Louisville", -85.759444),
			array("America/Kentucky/Monticello", -84.849167),
			array("America/Los_Angeles", -118.242778),
			array("America/Menominee", -87.614167),
			array("America/New_York", -74.006389),
			array("America/Nome", -165.406389),
			array("America/North_Dakota/Center", -101.299167),
			array("America/North_Dakota/New_Salem", -101.410833),
			array("America/Phoenix", -112.073333),
			array("America/Shiprock", -108.686389),
			array("America/Yakutat", -139.727222),
			array("Pacific/Honolulu", -157.858333)
		),
		"UY" => "America/Montevideo",
		"UZ" => array(
			array("Asia/Samarkand", 66.8),
			array("Asia/Tashkent", 69.3)
		),
		"VA" => "Europe/Vatican",
		"VC" => "America/St_Vincent",
		"VE" => "America/Caracas",
		"VG" => "America/Tortola",
		"VI" => "America/St_Thomas",
		"VN" => "Asia/Saigon",
		"VU" => "Pacific/Efate",
		"WF" => "Pacific/Wallis",
		"WS" => "Pacific/Apia",
		"YE" => "Asia/Aden",
		"YT" => "Indian/Mayotte",
		"ZA" => "Africa/Johannesburg",
		"ZM" => "Africa/Lusaka",
		"ZW" => "Africa/Harare"
	);
	
	public static function check(DateTimeZone $zone, string $country)
	{
		
		$lookup = self::$COUNTRY_TIME_ZONES[$country];
		$utc = new DateTime('now', new DateTimeZone('UTC'));
		
		if (is_string($lookup)) {
			$check = new DateTimeZone($lookup);
			return $check->getOffset($utc) === $zone->getOffset($utc);
		}
		
		foreach ($lookup as $_lookup) {
			$check = new DateTimeZone($_lookup[0]);
			
			if ($check->getOffset($utc) === $zone->getOffset($utc)) {
				return true;
			}
		}
		
		return false;
	}
}
