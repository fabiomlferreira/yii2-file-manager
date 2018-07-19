<?php

use yii\db\Migration;

class m141129_130551_create_filemanager_mediafile_table extends Migration
{
    public function up()
    {
        $this->createTable('filemanager_mediafile', [
            'id' => $this->primaryKey(),
            'filename' => $this->string(255)->notNull(),
            'type' => $this->string(255)->notNull(),
            'url' => $this->text()->notNull(),
            'alt' => $this->text(),
            'size' => $this->string(255)->notNull(),
            'description' => $this->text(),
            'thumbs' => $this->text(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('filemanager_mediafile');
    }
}
