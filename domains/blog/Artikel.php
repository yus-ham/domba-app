<?php

namespace app\domains\blog;

use app\core\auth\Identity;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string|null $post_title
 * @property string|null $post_content
 * @property string|null $post_excerpt
 * @property string|null $en_post_title
 * @property string|null $en_post_content
 * @property string|null $en_post_excerpt
 * @property string|null $post_status
 * @property string|null $post_name
 * @property string|null $post_type
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property string|null $published_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $hit
 * @property int $is_highlight
 */
class Artikel extends ActiveRecord
{
    const ST_DRAFT = 0;
    const ST_PUBLISHED = 1;

    public static function tableName()
    {
        return 'posts';
    }

    public function behaviors()
    {
        return [
            BlameableBehavior::class,
            new TimestampBehavior([
                'value' => fn() => sqlDate(),
            ])
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        
        $fields['created_by_name'] = function() {
            return $this->createdBy->name;
        };

        return $fields;
    }

    public function getCreatedBy()
    {
        return $this->hasOne(Identity::class, ['id' => 'created_by']);
    }
}