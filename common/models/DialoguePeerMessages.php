<?php

namespace common\models;

use common\models\helpers\TelegramBot;
use Yii;

/**
 * This is the model class for table "dialogue_peer_messages".
 *
 * @property int $id
 * @property int $peer_id
 * @property int $user_id
 * @property string $message
 * @property string|null $attachments
 * @property string $date
 * @property int $isSupport
 */
class DialoguePeerMessages extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'dialogue_peer_messages';
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if ($this->isSupport === 0) {
                $tg = new TelegramBot();
                $tg->new__message($tg::ticket__message($this->peer_id), $tg::PEER_SUPPORT);
                $tg->new__message($tg::ticket__message($this->peer_id), $tg::PEER_OPERATIONS);
            }
        }
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['peer_id', 'user_id', 'message'], 'required'],
            [['peer_id', 'user_id', 'isSupport'], 'integer'],
            [['message', 'attachments'], 'string'],
            [['date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'peer_id' => 'Peer ID',
            'user_id' => 'User ID',
            'message' => 'Message',
            'attachments' => 'Attachments',
            'date' => 'Date',
            'isSupport' => 'От модерации',
        ];
    }
}