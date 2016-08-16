<?php


namespace App\Model\Table;


use Cake\ORM\Table;


class FacebookAsyncJobsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('Timestamp');
    }

    /**
     * Get job by ID
     * @param int $id
     * @return array
     */
    public function getJob($id)
    {
        return $this->find()->where(['job_id'=>$id])->first();
    }

    /**
     * Create new job
     * @param  array $data
     * @return boolean
     */
    public function saveJob($data)
    {
        $entity = $this->newEntity($data);
        return $this->save($entity);
    }

    /**
     * Delete job by ID
     * @param  int $id
     * @return boolean
     */
    public function deleteJob($id)
    {
        $entity = $this->get($id);
        return $this->delete($entity);
    }

}
