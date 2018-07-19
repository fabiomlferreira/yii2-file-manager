<?php

use yii\db\Migration;

class m141203_173402_create_filemanager_owners_table extends Migration
{
    public function up()
    {
        $this->createTable('filemanager_owners', [
            'mediafile_id' => $this->integer()->notNull(),
            'owner_id' => $this->integer()->notNull(),
            'owner' => $this->string(255)->notNull(),
            'owner_attribute' => $this->string(255)->notNull(),
            'PRIMARY KEY (`mediafile_id`, `owner_id`, `owner`, `owner_attribute`)',
        ]);
    }

    public function down()
    {
        $this->dropTable('filemanager_owners');
    }
}
