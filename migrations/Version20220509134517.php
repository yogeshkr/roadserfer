<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220509134517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE equipment (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(100) NOT NULL, total_quantity INT NOT NULL, status TINYINT(1) DEFAULT \'1\' NOT NULL, price NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at datetime default current_timestamp on update current_timestamp not null, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, pickup_station INT NOT NULL, drop_station INT NOT NULL, user_id INT NOT NULL, booked_from DATE NOT NULL, booked_to DATE NOT NULL, total_amount NUMERIC(10, 2) DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at datetime default current_timestamp on update current_timestamp not null, INDEX IDX_F5299398F01BFE1C (pickup_station), INDEX IDX_F5299398A8D320A7 (drop_station), INDEX IDX_F5299398A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE order_detail (id INT AUTO_INCREMENT NOT NULL, order_id INT NOT NULL, equipment_id INT NOT NULL, price NUMERIC(10, 2) NOT NULL, quantity INT NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at datetime default current_timestamp on update current_timestamp not null, INDEX IDX_ED896F468D9F6D38 (order_id), INDEX IDX_ED896F46517FE9FE (equipment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE station (id INT AUTO_INCREMENT NOT NULL, station_name VARCHAR(255) NOT NULL, station_code VARCHAR(100) NOT NULL, status TINYINT(1) DEFAULT \'1\' NOT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at datetime default current_timestamp on update current_timestamp not null, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE station_inventory (id INT AUTO_INCREMENT NOT NULL, station_id INT NOT NULL, equipment_id INT NOT NULL, order_id INT DEFAULT NULL, quantity INT NOT NULL, inventory_date DATE NOT NULL, inventory_type INT NOT NULL, INDEX IDX_33A9242D21BDB235 (station_id), INDEX IDX_33A9242D517FE9FE (equipment_id), INDEX IDX_33A9242D8D9F6D38 (order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398F01BFE1C FOREIGN KEY (pickup_station) REFERENCES station (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A8D320A7 FOREIGN KEY (drop_station) REFERENCES station (id)');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F468D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_detail ADD CONSTRAINT FK_ED896F46517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE station_inventory ADD CONSTRAINT FK_33A9242D21BDB235 FOREIGN KEY (station_id) REFERENCES station (id)');
        $this->addSql('ALTER TABLE station_inventory ADD CONSTRAINT FK_33A9242D517FE9FE FOREIGN KEY (equipment_id) REFERENCES equipment (id)');
        $this->addSql('ALTER TABLE station_inventory ADD CONSTRAINT FK_33A9242D8D9F6D38 FOREIGN KEY (order_id) REFERENCES `order` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F46517FE9FE');
        $this->addSql('ALTER TABLE station_inventory DROP FOREIGN KEY FK_33A9242D517FE9FE');
        $this->addSql('ALTER TABLE order_detail DROP FOREIGN KEY FK_ED896F468D9F6D38');
        $this->addSql('ALTER TABLE station_inventory DROP FOREIGN KEY FK_33A9242D8D9F6D38');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398F01BFE1C');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A8D320A7');
        $this->addSql('ALTER TABLE station_inventory DROP FOREIGN KEY FK_33A9242D21BDB235');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP TABLE equipment');
        $this->addSql('DROP TABLE `order`');
        $this->addSql('DROP TABLE order_detail');
        $this->addSql('DROP TABLE station');
        $this->addSql('DROP TABLE station_inventory');
        $this->addSql('DROP TABLE user');
    }
}
