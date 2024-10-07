<?php
namespace App\Models;

use App\Core\Database;

class Model extends Database
{
    /* instance db */
    private $db;

    /* table db */
    protected $table;

    /**
     * model->find($id)
     * @param integer $id
     * @return void
     */
    public function find(int $id)
    {
        $query = $this->requete("SELECT * FROM {$this->table} WHERE id = $id");
        return $query->fetch();
    }

    /**
     * model->findName($name)
     * @param string $name
     * @return void
     */
    public function findName(string $name)
    {
        $query = $this->requete("SELECT * FROM {$this->table} WHERE name = '$name'");
        return $query->fetch();
    }

    /**
     * model->findBy(['key' => $value])
     * @param array $targets
     * @return array
     */
    public function findBy(array $targets): array
    {
        $fields = [];
        $values = [];

        foreach ($targets as $field => $value)
        {
            $fields[] = "$field = ?";
            $values[] = $value;
        }

        $list_fields = implode(' AND ', $fields);

        $query = $this->requete("SELECT * FROM {$this->table} WHERE $list_fields", $values);
        return $query->fetchAll();
    }

    /**
     * model->findAll();
     * @return array
     */
    public function findAll(): array
    {
        $query = $this->requete("SELECT * FROM " . $this->table);
        return $query->fetchAll();
    }

    /**
     * model->findAllOrderBy('id DESC')
     * @param string $orderBy
     * @return array
     */
    public function findAllOrderBy(string $orderBy): array
    {
        $query = $this->requete("SELECT * FROM {$this->table} ORDER BY $orderBy");
        return $query->fetchAll();
    }

    /**
     * model->findAllOrderByLimit('id DESC', 6);
     * @param string $orderBy
     * @param integer $limit
     * @return array
     */
    public function findAllOrderByLimit(string $orderBy, int $limit): array
    {
        $query = $this->requete("SELECT * FROM {$this->table} ORDER BY $orderBy LIMIT $limit");
        return $query->fetchAll();
    }

    /**
     * model->findAllPaginate('id DESC', 8, 1);
     * @param string $orderBy
     * @param integer $eachPerPage
     * @param integer $getId
     * @return array
     */
    public function findAllPaginate(string $orderBy, int $eachPerPage, int $getId): array
    {
        $start = ($getId -1) * $eachPerPage;
    
        $query = $this->requete("SELECT * FROM {$this->table} ORDER BY $orderBy LIMIT " . $start . ", " . $eachPerPage);
        return $query->fetchAll();
    }

    /**
     * model->countPaginate(8)
     * @param integer $eachPerPage
     * @return integer
     */
    public function countPaginate(int $eachPerPage): int
    {
        $query = $this->requete("SELECT COUNT(*) AS `count` FROM {$this->table}");

        if ($query->rowCount() > 0) { $countTotal = $query->fetch(); }

        $counts = ceil($countTotal->count / $eachPerPage);
        return $counts;
    }

    /**
     * crud : create
     * @return void
     */
    public function create()
    {
        $fields = [];
        $inter = [];
        $values = [];

        foreach ($this as $field => $value)
        {
            if ($value !== null && $field != 'db' && $field != 'table')
            {
                $fields[] = $field;
                $inter[] = "?";
                $values[] = $value;
            }
        }

        $list_fields = implode(', ', $fields);
        $list_inter = implode(', ', $inter);

        $query = $this->requete('INSERT INTO '.$this->table.' ('. $list_fields.')VALUES('.$list_inter.')', $values);
        return $query;
    }

    /**
     * crud : update
     * @return void
     */
    public function update()
    {
        $fields = [];
        $values = [];

        foreach ($this as $field => $value)
        {
            if ($value !== null && $field != 'db' && $field != 'table')
            {
                $fields[] = "$field = ?";
                $values[] = $value;
            }
        }
        $values[] = $this->id;

        $list_fields = implode(', ', $fields);

        $query = $this->requete('UPDATE '.$this->table.' SET '. $list_fields.' WHERE id = ?', $values);
        return $query;
    }

    /**
     * crud : delete
     * @param integer $id
     * @return void
     */
    public function delete(int $id)
    {
        $query = $this->requete("DELETE FROM {$this->table} WHERE id = ?", [$id]);
        return $query;
    }

    /**
     * hydrate datas
     * @param $datas
     * @return void
     */
    public function hydrate($datas)
    {
        foreach ($datas as $key => $value)
        {
            $method = 'set'.ucfirst($key);
            
            if (method_exists($this, $method))
            {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * requete
     * @param string $sql
     * @param array|null $attributes
     * @return void
     */
    protected function requete(string $sql, array $attributes = null)
    {
        $this->db = Database::getInstance();

        if ($attributes !== null)
        {
            $query = $this->db->prepare($sql);
            $query->execute($attributes);
            return $query;
        }

        else
        {
            return $this->db->query($sql);
        }
    }
}