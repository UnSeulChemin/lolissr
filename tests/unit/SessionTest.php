<?php

declare(strict_types=1);

use App\Core\Session;
use PHPUnit\Framework\TestCase;

final class SessionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_ACTIVE)
        {
            $_SESSION = [];
        }
    }

    protected function tearDown(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE)
        {
            $_SESSION = [];
        }

        parent::tearDown();
    }

    public function testSetStoresValueInSession(): void
    {
        Session::set('success', 'OK');

        $this->assertSame('OK', $_SESSION['success']);
    }

    public function testGetReturnsStoredValue(): void
    {
        Session::set('message', 'Bonjour');

        $this->assertSame('Bonjour', Session::get('message'));
    }

    public function testGetReturnsDefaultWhenKeyDoesNotExist(): void
    {
        $this->assertSame('fallback', Session::get('unknown', 'fallback'));
    }

    public function testHasReturnsTrueWhenKeyExists(): void
    {
        Session::set('errors', ['livre' => 'Le titre est obligatoire.']);

        $this->assertTrue(Session::has('errors'));
    }

    public function testHasReturnsFalseWhenKeyDoesNotExist(): void
    {
        $this->assertFalse(Session::has('unknown'));
    }

    public function testRemoveDeletesKey(): void
    {
        Session::set('notice', 'Test');

        Session::remove('notice');

        $this->assertFalse(Session::has('notice'));
    }

    public function testForgetDeletesMultipleKeys(): void
    {
        Session::set('success', 'OK');
        Session::set('error', 'Erreur');
        Session::set('old', ['livre' => 'One Piece']);

        Session::forget(['success', 'error']);

        $this->assertFalse(Session::has('success'));
        $this->assertFalse(Session::has('error'));
        $this->assertTrue(Session::has('old'));
    }

    public function testPullReturnsValueAndDeletesKey(): void
    {
        Session::set('flash', 'Message unique');

        $value = Session::pull('flash');

        $this->assertSame('Message unique', $value);
        $this->assertFalse(Session::has('flash'));
    }

    public function testPullReturnsDefaultWhenKeyDoesNotExist(): void
    {
        $value = Session::pull('missing', 'default-value');

        $this->assertSame('default-value', $value);
        $this->assertFalse(Session::has('missing'));
    }

    public function testFlashStoresValueLikeSet(): void
    {
        Session::flash('success', 'Manga ajouté');

        $this->assertSame('Manga ajouté', Session::get('success'));
    }
}