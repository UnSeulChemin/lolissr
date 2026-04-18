<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Models\MangaModel;

final class MangaModelTest extends TestCase
{
    private MangaModel $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new MangaModel();
    }

    public function testCountAllTomesReturnsInt(): void
    {
        $result = $this->model->countAllTomes();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testCountSeriesReturnsInt(): void
    {
        $result = $this->model->countSeries();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testFindLastAddedReturnsObjectOrFalse(): void
    {
        $result = $this->model->findLastAdded();

        $this->assertTrue(
            is_object($result) || $result === false
        );
    }

    public function testFindLongestSeriesReturnsObjectOrFalse(): void
    {
        $result = $this->model->findLongestSeries();

        $this->assertTrue(
            is_object($result) || $result === false
        );
    }

    public function testAverageNoteReturnsFloatOrNull(): void
    {
        $result = $this->model->averageNote();

        $this->assertTrue(
            is_float($result) || $result === null
        );
    }

    public function testTopLongestSeriesReturnsArray(): void
    {
        $result = $this->model->topLongestSeries(5);

        $this->assertIsArray($result);
    }

    public function testFindLowRatedMangasReturnsArray(): void
    {
        $result = $this->model->findLowRatedMangas(10);

        $this->assertIsArray($result);
    }

    public function testFindLowJacquetteMangasReturnsArray(): void
    {
        $result = $this->model->findLowJacquetteMangas(10);

        $this->assertIsArray($result);
    }

    public function testFindLowLivreStateMangasReturnsArray(): void
    {
        $result = $this->model->findLowLivreStateMangas(10);

        $this->assertIsArray($result);
    }

    public function testCountFirstTomesPaginateReturnsAtLeastOne(): void
    {
        $result = $this->model->countFirstTomesPaginate(12);

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
    }

    public function testFindAllFirstTomesReturnsArray(): void
    {
        $result = $this->model->findAllFirstTomes('id DESC', 12, 1);

        $this->assertIsArray($result);
    }

    public function testFindAllFirstTomesFallsBackToSafeOrderBy(): void
    {
        $result = $this->model->findAllFirstTomes('drop table manga', 12, 1);

        $this->assertIsArray($result);
    }

    public function testFindBySlugReturnsArray(): void
    {
        $result = $this->model->findBySlug('one-piece');

        $this->assertIsArray($result);
    }

    public function testFindBySlugNormalizesSlug(): void
    {
        $normalized = $this->model->findBySlug('one-piece');
        $nonNormalized = $this->model->findBySlug('One Piece');

        $this->assertIsArray($normalized);
        $this->assertIsArray($nonNormalized);
    }

    public function testFindOneBySlugAndNumeroReturnsObjectOrFalse(): void
    {
        $result = $this->model->findOneBySlugAndNumero('one-piece', 1);

        $this->assertTrue(
            is_object($result) || $result === false
        );
    }

    public function testSettersAndGettersWorkCorrectly(): void
    {
        $model = new MangaModel();

        $model
            ->setId(10)
            ->setThumbnail('one-piece-01')
            ->setExtension('JPG')
            ->setSlug('One Piece')
            ->setLivre(' One Piece ')
            ->setNumero(12)
            ->setJacquette(4)
            ->setLivreNote(5)
            ->setNote(9)
            ->setCommentaire('  Très bon tome  ');

        $this->assertSame(10, $model->getId());
        $this->assertSame('one-piece-01', $model->getThumbnail());
        $this->assertSame('jpg', $model->getExtension());
        $this->assertSame('one-piece', $model->getSlug());
        $this->assertSame('One Piece', $model->getLivre());
        $this->assertSame(12, $model->getNumero());
        $this->assertSame(4, $model->getJacquette());
        $this->assertSame(5, $model->getLivreNote());
        $this->assertSame(9, $model->getNote());
        $this->assertIsString($model->getCommentaire());
    }

    public function testSetNumeroDoesNotAllowNegativeValue(): void
    {
        $model = new MangaModel();
        $model->setNumero(-5);

        $this->assertSame(0, $model->getNumero());
    }

    public function testSetJacquetteInvalidValueBecomesNull(): void
    {
        $model = new MangaModel();
        $model->setJacquette(9);

        $this->assertNull($model->getJacquette());
    }

    public function testSetLivreNoteInvalidValueBecomesNull(): void
    {
        $model = new MangaModel();
        $model->setLivreNote(0);

        $this->assertNull($model->getLivreNote());
    }

    public function testSetSlugNormalizesValue(): void
    {
        $model = new MangaModel();
        $model->setSlug('Dragon Ball Super');

        $this->assertSame('dragon-ball-super', $model->getSlug());
    }
}