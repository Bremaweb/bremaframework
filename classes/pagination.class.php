<?php


class pagination {
    private $page = 0;
    private $perPage = 0;
    private $total = 0;
    private $pages = 0;
    private $results = null;
    private $db = null;

    public function __construct($dbCon, $page = null, $perPage = 20){
        $this->db = $dbCon;
        $this->page = $page === null ? getPage() : $page;
        $this->perPage = $perPage;

    }

    public function getResults($query){
        $this->results = $this->db->query($query);
        $this->total = $this->db->numrows();
        $this->pages = ceil($this->total / $this->perPage);

        $this->db->seek($this->perPage * ($this->page - 1));
        $count = 0;

        $rows = array();
        while ( $row = $this->db->fetchRow($this->results) ){
            $rows[] = $row;
            if ( $count >= $this->perPage ){
                break;
            }
        }

        return array(
            'total' => $this->total,
            'pages' => $this->pages,
            'rows' => $rows
        );
    }

    public function pageLinks(){
        $linkHtml = "<div>";
        if ( $this->page > 1 ){
            // previous page arrow
            $linkHtml .= "<a href=''>&lt;</a>";
        }

        $linkHtml .= "</div>";

        return $linkHtml;
    }

    public static function getPage(){
        return !empty($_GET['page']) ? $_GET['page'] : 0;
    }

}