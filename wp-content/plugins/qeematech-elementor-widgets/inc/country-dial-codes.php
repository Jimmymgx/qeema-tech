<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Static ISO-country / dial-code reference data for the Contact page's
 * "الدولة" field (converted from free-text to a select — see
 * geo-country-detect-endpoint.php for the IP-based default). Arab/Middle-East
 * countries first (this is an Egypt-based company's primary audience), then
 * other major countries. Neutral reference data, not business content.
 */
function qeema_get_country_dial_codes() {
	return array(
		array( 'iso2' => 'EG', 'name_ar' => 'مصر', 'dial' => '+20' ),
		array( 'iso2' => 'SA', 'name_ar' => 'السعودية', 'dial' => '+966' ),
		array( 'iso2' => 'AE', 'name_ar' => 'الإمارات', 'dial' => '+971' ),
		array( 'iso2' => 'KW', 'name_ar' => 'الكويت', 'dial' => '+965' ),
		array( 'iso2' => 'QA', 'name_ar' => 'قطر', 'dial' => '+974' ),
		array( 'iso2' => 'BH', 'name_ar' => 'البحرين', 'dial' => '+973' ),
		array( 'iso2' => 'OM', 'name_ar' => 'عُمان', 'dial' => '+968' ),
		array( 'iso2' => 'JO', 'name_ar' => 'الأردن', 'dial' => '+962' ),
		array( 'iso2' => 'LB', 'name_ar' => 'لبنان', 'dial' => '+961' ),
		array( 'iso2' => 'SY', 'name_ar' => 'سوريا', 'dial' => '+963' ),
		array( 'iso2' => 'IQ', 'name_ar' => 'العراق', 'dial' => '+964' ),
		array( 'iso2' => 'PS', 'name_ar' => 'فلسطين', 'dial' => '+970' ),
		array( 'iso2' => 'YE', 'name_ar' => 'اليمن', 'dial' => '+967' ),
		array( 'iso2' => 'LY', 'name_ar' => 'ليبيا', 'dial' => '+218' ),
		array( 'iso2' => 'SD', 'name_ar' => 'السودان', 'dial' => '+249' ),
		array( 'iso2' => 'TN', 'name_ar' => 'تونس', 'dial' => '+216' ),
		array( 'iso2' => 'DZ', 'name_ar' => 'الجزائر', 'dial' => '+213' ),
		array( 'iso2' => 'MA', 'name_ar' => 'المغرب', 'dial' => '+212' ),
		array( 'iso2' => 'MR', 'name_ar' => 'موريتانيا', 'dial' => '+222' ),
		array( 'iso2' => 'SO', 'name_ar' => 'الصومال', 'dial' => '+252' ),
		array( 'iso2' => 'DJ', 'name_ar' => 'جيبوتي', 'dial' => '+253' ),
		array( 'iso2' => 'KM', 'name_ar' => 'جزر القمر', 'dial' => '+269' ),
		array( 'iso2' => 'TR', 'name_ar' => 'تركيا', 'dial' => '+90' ),
		array( 'iso2' => 'US', 'name_ar' => 'الولايات المتحدة', 'dial' => '+1' ),
		array( 'iso2' => 'CA', 'name_ar' => 'كندا', 'dial' => '+1' ),
		array( 'iso2' => 'GB', 'name_ar' => 'المملكة المتحدة', 'dial' => '+44' ),
		array( 'iso2' => 'DE', 'name_ar' => 'ألمانيا', 'dial' => '+49' ),
		array( 'iso2' => 'FR', 'name_ar' => 'فرنسا', 'dial' => '+33' ),
		array( 'iso2' => 'IT', 'name_ar' => 'إيطاليا', 'dial' => '+39' ),
		array( 'iso2' => 'ES', 'name_ar' => 'إسبانيا', 'dial' => '+34' ),
		array( 'iso2' => 'NL', 'name_ar' => 'هولندا', 'dial' => '+31' ),
		array( 'iso2' => 'BE', 'name_ar' => 'بلجيكا', 'dial' => '+32' ),
		array( 'iso2' => 'CH', 'name_ar' => 'سويسرا', 'dial' => '+41' ),
		array( 'iso2' => 'SE', 'name_ar' => 'السويد', 'dial' => '+46' ),
		array( 'iso2' => 'NO', 'name_ar' => 'النرويج', 'dial' => '+47' ),
		array( 'iso2' => 'DK', 'name_ar' => 'الدنمارك', 'dial' => '+45' ),
		array( 'iso2' => 'AT', 'name_ar' => 'النمسا', 'dial' => '+43' ),
		array( 'iso2' => 'PT', 'name_ar' => 'البرتغال', 'dial' => '+351' ),
		array( 'iso2' => 'GR', 'name_ar' => 'اليونان', 'dial' => '+30' ),
		array( 'iso2' => 'PL', 'name_ar' => 'بولندا', 'dial' => '+48' ),
		array( 'iso2' => 'RU', 'name_ar' => 'روسيا', 'dial' => '+7' ),
		array( 'iso2' => 'UA', 'name_ar' => 'أوكرانيا', 'dial' => '+380' ),
		array( 'iso2' => 'CN', 'name_ar' => 'الصين', 'dial' => '+86' ),
		array( 'iso2' => 'JP', 'name_ar' => 'اليابان', 'dial' => '+81' ),
		array( 'iso2' => 'KR', 'name_ar' => 'كوريا الجنوبية', 'dial' => '+82' ),
		array( 'iso2' => 'IN', 'name_ar' => 'الهند', 'dial' => '+91' ),
		array( 'iso2' => 'PK', 'name_ar' => 'باكستان', 'dial' => '+92' ),
		array( 'iso2' => 'BD', 'name_ar' => 'بنغلاديش', 'dial' => '+880' ),
		array( 'iso2' => 'ID', 'name_ar' => 'إندونيسيا', 'dial' => '+62' ),
		array( 'iso2' => 'MY', 'name_ar' => 'ماليزيا', 'dial' => '+60' ),
		array( 'iso2' => 'PH', 'name_ar' => 'الفلبين', 'dial' => '+63' ),
		array( 'iso2' => 'SG', 'name_ar' => 'سنغافورة', 'dial' => '+65' ),
		array( 'iso2' => 'TH', 'name_ar' => 'تايلاند', 'dial' => '+66' ),
		array( 'iso2' => 'VN', 'name_ar' => 'فيتنام', 'dial' => '+84' ),
		array( 'iso2' => 'AU', 'name_ar' => 'أستراليا', 'dial' => '+61' ),
		array( 'iso2' => 'NZ', 'name_ar' => 'نيوزيلندا', 'dial' => '+64' ),
		array( 'iso2' => 'ZA', 'name_ar' => 'جنوب أفريقيا', 'dial' => '+27' ),
		array( 'iso2' => 'NG', 'name_ar' => 'نيجيريا', 'dial' => '+234' ),
		array( 'iso2' => 'KE', 'name_ar' => 'كينيا', 'dial' => '+254' ),
		array( 'iso2' => 'ET', 'name_ar' => 'إثيوبيا', 'dial' => '+251' ),
		array( 'iso2' => 'GH', 'name_ar' => 'غانا', 'dial' => '+233' ),
		array( 'iso2' => 'BR', 'name_ar' => 'البرازيل', 'dial' => '+55' ),
		array( 'iso2' => 'MX', 'name_ar' => 'المكسيك', 'dial' => '+52' ),
		array( 'iso2' => 'AR', 'name_ar' => 'الأرجنتين', 'dial' => '+54' ),
		array( 'iso2' => 'CL', 'name_ar' => 'تشيلي', 'dial' => '+56' ),
		array( 'iso2' => 'CO', 'name_ar' => 'كولومبيا', 'dial' => '+57' ),
	);
}

/**
 * Converts an ISO 3166-1 alpha-2 code (e.g. "EG") to its flag emoji, via the
 * standard regional-indicator-symbol technique (each letter A-Z maps to the
 * Unicode codepoint U+1F1E6..U+1F1FF, offset +127397 from its ASCII value).
 */
function qeema_iso2_to_flag_emoji( $iso2 ) {
	$iso2 = strtoupper( $iso2 );
	if ( 2 !== strlen( $iso2 ) ) {
		return '';
	}
	$flag = '';
	for ( $i = 0; $i < 2; $i++ ) {
		$flag .= mb_chr( 127397 + ord( $iso2[ $i ] ), 'UTF-8' );
	}
	return $flag;
}

/**
 * Builds the Elementor Pro select field's `field_options` string
 * ("label|value" per line) from the dataset above.
 */
function qeema_build_country_field_options() {
	$lines   = array();
	$lines[] = 'اختر الدولة|اختر الدولة';
	foreach ( qeema_get_country_dial_codes() as $country ) {
		$flag    = qeema_iso2_to_flag_emoji( $country['iso2'] );
		$label   = trim( $flag . ' ' . $country['name_ar'] . ' (' . $country['dial'] . ')' );
		$lines[] = $label . '|' . $country['dial'];
	}
	return implode( "\n", $lines );
}
