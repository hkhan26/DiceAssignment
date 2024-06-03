<?php

require __DIR__ . "/class-wp-html-token.php";
require __DIR__ . "/class-wp-html-span.php";
require __DIR__ . "/class-wp-html-text-replacement.php";
require __DIR__ . "/class-wp-html-decoder.php";
require __DIR__ . "/class-wp-html-attribute-token.php";
require __DIR__ . "/class-wp-xml-decoder.php";
require __DIR__ . "/class-wp-xml-tag-processor.php";

$processor = new WP_XML_Tag_Processor( '<root>
    <span>
        Im inside
        </input>
    </span>
    <div>Heyya</div>
</root>' );
$processor->declare_element_as_pcdata('span');

// $wxr = file_get_contents(__DIR__ . '/test.wxr');
// $processor = new WP_XML_Tag_Processor( $wxr );
while( $processor->step() ) {
    echo "\n " . dump_token($processor);
}

function dump_token(WP_XML_Tag_Processor $p) {
    $result = $p->get_token_type() . ' ';
    switch($p->get_token_type()) {
        case '#tag':
            $result .= '(' . $p->get_token_name() . ')' . ' IN ' . implode( ' > ', $p->get_breadcrumbs() );
            break;
        case '#text':
            $result .= '(' . preg_replace('~\s+~', ' ', $p->get_modifiable_text()) . ')';
            break;
    }
    return $result;
}