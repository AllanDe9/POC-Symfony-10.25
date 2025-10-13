<?php

namespace App\DataFixtures;

use App\Entity\Editor;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EditorFixtures extends Fixture
{
    public const EDITOR_REFERENCE = 'editor_';

    public static function getGroups(): array
    {
        return ['editors'];
    }
    
    public function load(ObjectManager $manager): void
    {
        $editors = [
            ['name' => 'Ubisoft', 'country' => 'France'],
            ['name' => 'Electronic Arts', 'country' => 'USA'],
            ['name' => 'Nintendo', 'country' => 'Japan'],
        ];

        foreach ($editors as $i => $data) {
            $editor = new Editor();
            $editor->setName($data['name']);
            $editor->setCountry($data['country']);
            $manager->persist($editor);
            $this->addReference(self::EDITOR_REFERENCE . $i, $editor);
        }

        $manager->flush();
    }
}
