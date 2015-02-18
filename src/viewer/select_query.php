<?php
/*
Copyright 2008 Josh Heidenreich

This file is part of Pelzini.

Pelzini is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Pelzini is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Pelzini.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * A system for the automatic creation of select queries
 *
 * @package Viewer
 * @author Josh Heidenreich
 * @since 0.2
 **/


/**
 * This class simplifies the creation of select queries
 **/
class SelectQuery
{
    private $fields;
    private $from;
    private $joins;
    private $where;
    private $group;
    private $order;


    public function __construct()
    {
        $this->joins = array();
        $this->where = array();
    }


    /**
     * Generic method for adding fields to the query
     *
     * @param string The fields to add to the query
     **/
    public function addFields($fields)
    {
        if ($this->fields) $this->fields .= ', ';
        $this->fields .= $fields;
    }


    /**
     * Sets the FROM clause for the query
     **/
    public function setFrom($from)
    {
        $this->from = $from;
    }


    /**
     * Adds an INNER JOIN
     **/
    public function addInnerJoin($join)
    {
        $this->joins[] = 'INNER JOIN ' . $join;
    }


    /**
     * Adds a LEFT JOIN
     **/
    public function addLeftJoin($join)
    {
        $this->joins[] = 'LEFT JOIN ' . $join;
    }


    /**
     * Adds a WHERE clause. Where clauses are ANDed together
     **/
    public function addWhere($where)
    {
        $this->where[] = $where;
    }


    /**
     * Sets a GROUP BY clause.
     **/
    public function setGroupBy($group_by)
    {
        $this->group = $group_by;
    }


    /**
     * Sets an ORDER BY clause.
     **/
    public function setOrderBy($order_by)
    {
        $this->order = $order_by;
    }


    /**
     * This creates the SQL query
     **/
    public function buildQuery()
    {
        $q  = "SELECT {$this->fields}\n";
        $q .= "  FROM {$this->from}\n";

        foreach ($this->joins as $join) {
            $q .= "  {$join}\n";
        }

        if (count($this->where)) {
            $q .= "  WHERE " . implode(' AND ', $this->where) . "\n";
        }

        if ($this->group) {
            $q .= "  GROUP BY {$this->group}\n";
        }

        if ($this->order) {
            $q .= "  ORDER BY {$this->order}\n";
        }

        return $q;
    }


    /**
     * Adds the required WHERE clauses for checking the since version of this item matches what is required
     **/
    public function addSinceVersionWhere()
    {
        if (! $_SESSION['current_version']) return;

        $this->addWhere("({$this->from}.sinceid >= {$_SESSION['current_version']} OR {$this->from}.sinceid IS NULL)");
    }


}


?>
