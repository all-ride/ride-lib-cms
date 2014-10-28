<?php

namespace ride\library\cms\node;

/**
 * Container for a node in the trash
 */
class TrashNode {

    /**
     * Id of the trash node
     * @var string
     */
    protected $id;

    /**
     * Timestamp of removal
     * @var integer
     */
    protected $date;

    /**
     * Instance of the node
     * @var Node
     */
    protected $node;

    /**
     * Constructs a new trash node
     * @param Node $node Removed node
     * @param integer $date Timestamp of removal
     * @return null
     */
    public function __construct($id, Node $node, $date = null) {
        if ($date === null) {
            $date = time();
        }

        $this->id = $id;
        $this->date = $date;
        $this->node = $node;
    }

    /**
     * Gets the id within the trash
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Gets the date
     * @return integer Timestamp of removal
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Gets the removed node
     * @return Node
     */
    public function getNode() {
        return $this->node;
    }

}
