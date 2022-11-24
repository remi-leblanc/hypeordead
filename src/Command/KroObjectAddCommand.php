<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use Symfony\Component\Console\Question\Question;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class KroObjectAddCommand extends Command
{
	protected static $defaultName = 'kro:object:add';

	private $em;
	private $appEntities;

	public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
	{

		$this->em = $em;

		$this->passwordHasher = $passwordHasher;

		$appEntities = [];
		$metas = $this->em->getMetadataFactory()->getAllMetadata();
		foreach ($metas as $meta) {
			$this->appEntities[] = $meta->getName();
		}

		parent::__construct();
	}

	protected function configure()
	{
		$this
			->setDescription('Add a new object to the database.')
			->setHelp('Helps you to manually add a new object to the database.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{

		$io = new SymfonyStyle($input, $output);

		$requireArrayHint = 'separate values with commas';
		$requireRolesHint = 'must start with ROLE_';
		$requireUniqueHint = 'this field must be unique';

		$entityName = $io->choice('Select entity', $this->appEntities);
		$entityMetadata = $this->em->getClassMetadata($entityName);
		$entityCols = $entityMetadata->getColumnNames();

		$newEntity = new $entityName();

		foreach ($entityCols as $col) {

			$kebabCaseArray = explode('_', $col);
			$setter = 'set';
			foreach ($kebabCaseArray as $part) {
				$setter .= ucfirst($part);
			}

			if (method_exists($newEntity, $setter)) {

				if ($col == 'roles') {
					$newEntityValue = $io->ask('Enter new entity ' . $col . ' (' . $requireArrayHint . ', ' . $requireRolesHint . ')', null, function ($userInput) {
						$userInputArray = array_map('trim', explode(',', $userInput));
						foreach ($userInputArray as $key => $input) {
							if (strpos($input, 'ROLE_') === 0) {
								$userInputArray[$key] = strtoupper(preg_replace('/\s+/', '_', $input));
							} else {
								throw new \RuntimeException('Roles must start with ROLE_');
							}
						}
						return $userInputArray;
					});
				} else if ($entityMetadata->getTypeOfColumn($col) == 'array') {
					$newEntityValue = $io->ask('Enter new entity ' . $col . ' (' . $requireArrayHint . ')', null, function ($userInput) {
						return array_map('trim', explode(',', $userInput));
					});
				} else if ($col == 'password') {
					$newEntityValue = $io->askHidden('Enter new entity ' . $col);
					$newEntityValue = $this->passwordHasher->hashPassword($newEntity, $newEntityValue);
				} else if ($entityMetadata->isUniqueField($entityMetadata->getFieldName($col))) {
					$newEntityValue = $io->ask('Enter new entity ' . $col . ' (' . $requireUniqueHint . ')', null, function ($userInput) use ($entityName, $col) {
						$checkIfExistInDB = $this->em->getRepository($entityName)->findOneBy([$col => $userInput]);
						if ($checkIfExistInDB) {
							throw new \RuntimeException('This value already exist for this field');
						} else {
							return $userInput;
						}
					});
				} else if ($col == 'updated_at' || $col == 'created_at') {
					$newEntityValue = new \DateTimeImmutable();
				} else {
					$newEntityValue = $io->ask('Enter new entity ' . $col);
				}

				$newEntity->$setter($newEntityValue);
			}
		}

		$this->em->persist($newEntity);
		$this->em->flush();

		$io->success('Nice! A new ' . $entityName . ' object has been added to the database.');

		return 0;
	}
}
