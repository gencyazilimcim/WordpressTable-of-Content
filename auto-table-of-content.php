<?php

class table_of_content_auto {
    public function __construct(){
		add_filter( 'the_content', array($this, 'auto_id_headings') );
        add_filter( 'the_content', array($this, 'titleListAdd') );
    }

    public function auto_id_headings( $content ) {
        ob_start();
        $content = preg_replace_callback( '/(\<h[1-6](.*?))\>(.*)(<\/h[1-6]>)/i', function( $matches ) {
            if ( ! stripos( $matches[0], 'id=' ) ) :
                $matches[0] = $matches[1] . $matches[2] . ' id="' . sanitize_title( $matches[3] ) . '">' . $matches[3] . $matches[4];
            endif;
            return $matches[0];
        }, $content );
        return $content;
        ob_get_clean();
    }

    public function getTitleList(string $htmlContent ) {
        preg_match_all("#<h(\d)[^>]*?>(.*?)<[^>]*?/h\d>#i",$htmlContent, $headings, PREG_PATTERN_ORDER);

        $r = array();
        if( !empty( $headings[1] ) && !empty( $headings[2] ) ){
            $tags = $headings[1];
            $titles = $headings[2];
            foreach ($tags as $i => $tag) {
                $r[] = array( 'tag' => $tag, 'title' => $titles[ $i ] );
            }
        }
        return $r;
    }

    public function titleListAdd( $content ) {	
        ob_start();
        $titleList = $this->getTitleList($content); 

        $previousLevel = 0;
        $custom_content = '<ul>';
        
        foreach($titleList as  $headerElement){
            $level = $headerElement['tag'];

            if ($level > $previousLevel && $previousLevel > 0) {
                $custom_content .= '<ul>';
            }elseif ($level < $previousLevel) { 
                $custom_content .= str_repeat('</ul>', $previousLevel - $level);
            }

            $custom_content .= '<li><a href="#'.sanitize_title($headerElement['title']).'">'.$headerElement['title'].'</a></li>';

            $previousLevel = $level;
        }
        
        $custom_content .= str_repeat('</ul>', $level ?? 0);
		$endContent = $custom_content;
        $endContent .= $content;
        return $endContent;
        ob_get_clean();
    }
}

new table_of_content_auto();
