<?php
namespace App\Models;

use App\Models\Trait\CreatedAtTrait;

class AnimeModel extends Model
{
    /* containt created_at */
    use CreatedAtTrait;

    /* key primary id */
    protected int $id;

    /* column anime */
    protected string $anime;

    /* column origin */
    protected string $origin;

    /* column season */
    protected string $season;

    /* column episode */
    protected string $episode;

    /* column end */
    protected string $end;

    /* column note */
    protected string $note;

    /* magic method __construct */
    public function __construct()
    {
        $this->table = 'anime';
    }

    /**
     * getter id
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * setter id
     * @param integer $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * getter anime
     * @return string
     */
    public function getAnime(): string
    {
        return $this->anime;
    }

    /**
     * setter anime
     * @param string $anime
     * @return self
     */
    public function setAnime(string $anime): self
    {
        $this->anime = $anime;
        return $this;
    }

    /**
     * getter origin
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * setter origin
     * @param string $origin
     * @return self
     */
    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * getter season
     * @return string
     */
    public function getSeason(): string
    {
        return $this->season;
    }

    /**
     * setter season
     * @param string $season
     * @return self
     */
    public function setSeason(string $season): self
    {
        $this->season = $season;
        return $this;
    }

    /**
     * getter episode
     * @return string
     */
    public function getEpisode(): string
    {
        return $this->episode;
    }

    /**
     * setter episode
     * @param string $episode
     * @return self
     */
    public function setEpisode(string $episode): self
    {
        $this->episode = $episode;
        return $this;
    }

    /**
     * getter end
     * @return string
     */
    public function getEnd(): string
    {
        return $this->end;
    }

    /**
     * setter end
     * @param string $end
     * @return self
     */
    public function setEnd(string $end): self
    {
        $this->end = $end;
        return $this;
    }

    /**
     * getter note
     * @return string
     */
    public function getNote(): string
    {
        return $this->note;
    }

    /**
     * setter note
     * @param string $note
     * @return self
     */
    public function setNote(string $note): self
    {
        $this->note = $note;
        return $this;
    }
}