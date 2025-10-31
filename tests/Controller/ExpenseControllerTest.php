<?php

namespace App\Tests\Controller;

use App\Entity\Expense;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\DataFixtures\AppFixtures;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ExpenseControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $em = null;
    private $client;
    private string $jwtToken;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->em = $this->client->getContainer()->get('doctrine')->getManager();

        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $schemaTool = new SchemaTool($this->em);
            $schemaTool->createSchema($metadata);
        }

        $fixture = new AppFixtures();
        $fixture->load($this->em);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'user@example.com']);
        $jwtManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $this->jwtToken = $jwtManager->create($user);
    }

    public function testDeleteExpense(): void
    {
        $expense = $this->em->getRepository(Expense::class)->findOneBy([]);
        $this->assertNotNull($expense);

        $expenseId = $expense->getId();

        $this->client->request(
            'DELETE',
            '/api/expenses/' . $expenseId,
            [],
            [],
            ['HTTP_Authorization' => 'Bearer ' . $this->jwtToken]
        );

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $data);
        $this->assertEquals('Expense deleted', $data['message']);

        $deletedExpense = $this->em->getRepository(Expense::class)->find($expenseId);
        $this->assertNull($deletedExpense);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}
