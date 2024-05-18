<?php

namespace app\core\auth;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;


class RoleModuleSearch extends RoleModule
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id'], 'safe'],
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
        $query = Module::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 0],
        ]);

        $this->load($params, '');

        if (!$this->validate()) {
            // $query->where('0=1'); // return 0 records
            return $dataProvider;
        }

        $query->orderBy('name');

        return [
            'modules' => $dataProvider,
            'groupModules' => RoleModule::findAll(['role' => $this->role_id]),
        ];
    }
}
