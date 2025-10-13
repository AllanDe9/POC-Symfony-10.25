<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\VideoGame;
use App\Entity\Editor;
use App\Entity\Category;
use App\DataFixtures\CategoryFixtures;
use App\DataFixtures\EditorFixtures;

class VideoGameFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $videoGames = [
            [
                'title' => "Assassin's Creed",
                'editor_ref' => EditorFixtures::EDITOR_REFERENCE . '0', // Ubisoft
                'category_ref' => CategoryFixtures::CATEGORY_REFERENCE . '0', // Action
                'releaseDate' => '2007-11-13',
                'description' => 'Un jeu d\'action-aventure historique développé par Ubisoft.'
            ],
            [
                'title' => 'FIFA 25',
                'editor_ref' => EditorFixtures::EDITOR_REFERENCE . '1', // Electronic Arts
                'category_ref' => CategoryFixtures::CATEGORY_REFERENCE . '0', // Action
                'releaseDate' => '2025-09-29',
                'description' => 'Le dernier opus de la célèbre franchise de football.'
            ],
            [
                'title' => 'The Legend of Zelda',
                'editor_ref' => EditorFixtures::EDITOR_REFERENCE . '2', // Nintendo
                'category_ref' => [
                    CategoryFixtures::CATEGORY_REFERENCE . '1', // Aventure
                    CategoryFixtures::CATEGORY_REFERENCE . '2', // RPG (par exemple)
                ],
                'releaseDate' => '1986-02-21',
                'description' => 'Un jeu d\'aventure emblématique de Nintendo.'
            ],
            [
                'title' => 'Fire Emblem',
                'editor_ref' => EditorFixtures::EDITOR_REFERENCE . '2', // Nintendo
                'category_ref' => CategoryFixtures::CATEGORY_REFERENCE . '3', // Stratégie
                'releaseDate' => '1990-04-20',
                'description' => 'Un jeu de stratégie tactique développé par Nintendo.'
            ],
            [
                'title' => 'Mass Effect',
                'editor_ref' => EditorFixtures::EDITOR_REFERENCE . '1', // Electronic Arts
                'category_ref' => CategoryFixtures::CATEGORY_REFERENCE . '2', // RPG
                'releaseDate' => '2007-11-20',
                'description' => 'Un RPG de science-fiction développé par BioWare.'
            ],
        ];

        foreach ($videoGames as $data) {
            $videoGame = new VideoGame();
            $videoGame->setTitle($data['title']);
            $videoGame->setReleaseDate(new \DateTime($data['releaseDate']));
            $videoGame->setDescription($data['description']);
            $videoGame->setEditor($this->getReference($data['editor_ref'], Editor::class));
            $categoryRefs = (array) $data['category_ref'];
            foreach ($categoryRefs as $categoryRef) {
                $videoGame->addCategory($this->getReference($categoryRef, Category::class));
            }
            $manager->persist($videoGame);
        }

        $manager->flush();
    }
}
