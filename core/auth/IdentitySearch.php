<?php

namespace app\core\auth;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class IdentitySearch extends Identity
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status', 'trash'], 'integer'],
            [['company','name', 'firstname', 'lastname', 'username', 'auth_key', 'password_hash', 'password_reset_token', 'otp', 'email', 'created_at', 'created_by', 'modified_at', 'modified_by', 'birth_date', 'access_at', 'usertype', 'photo', 'phone', 'mobilephone', 'regency', 'city', 'state', 'country', 'address', 'zipcode', 'lat', 'long', 'gender', 'superadmin', 'description', 'en_description', 'jabatan', 'en_jabatan', 'password_hash2'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function apply($params)
    {
        $query = User::find()->alias('u')->joinWith(['unitKerja uk']);


        $dataProvider = new ActiveDataProvider([
            'key' => 'id',
            'query' => $query,
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // $query->where('0=1'); // return 0 records
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'trash' => $this->trash,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'uk.name', $this->company])
            ->andFilterWhere(['like', 'lastname', $this->lastname])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'password_reset_token', $this->password_reset_token])
            ->andFilterWhere(['like', 'otp', $this->otp])
            ->andFilterWhere(['like', 'u.email', $this->email])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'created_by', $this->created_by])
            ->andFilterWhere(['like', 'modified_at', $this->modified_at])
            ->andFilterWhere(['like', 'modified_by', $this->modified_by])
            ->andFilterWhere(['like', 'birth_date', $this->birth_date])
            ->andFilterWhere(['like', 'access_at', $this->access_at])
            ->andFilterWhere(['like', 'usertype', $this->usertype])
            ->andFilterWhere(['like', 'photo', $this->photo])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'mobilephone', $this->mobilephone])
            ->andFilterWhere(['like', 'regency', $this->regency])
            ->andFilterWhere(['like', 'city', $this->city])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'country', $this->country])
            ->andFilterWhere(['like', 'address', $this->address])
            ->andFilterWhere(['like', 'zipcode', $this->zipcode])
            ->andFilterWhere(['like', 'lat', $this->lat])
            ->andFilterWhere(['like', 'long', $this->long])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'superadmin', $this->superadmin])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'en_description', $this->en_description])
            ->andFilterWhere(['like', 'jabatan', $this->jabatan])
            ->andFilterWhere(['like', 'en_jabatan', $this->en_jabatan])
            ->andFilterWhere(['like', 'password_hash2', $this->password_hash2]);

        return $dataProvider;
    }
}
