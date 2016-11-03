<?php namespace Lego\Data\Table;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Lego\Data\Row\Row;

class ArrayTable extends Table
{
    protected function initialize()
    {
        $this->rows = collect($this->original())
            ->map(function ($data) {
                return lego_row($data);
            });
    }

    /**
     * 当前属性是否等于某值
     * @param $attribute
     * @param null $value
     * @return static
     */
    public function whereEquals($attribute, $value)
    {
        $this->rows = $this->rows->where($attribute, $value);

        return $this;
    }

    /**
     * 当前属性大于某值
     * @param $attribute
     * @param null $value
     * @param bool $equals 是否包含等于的情况, 默认不包含
     * @return static
     */
    public function whereGt($attribute, $value, bool $equals = false)
    {
        return $this->addFilterToRows(
            function (Row $row) use ($attribute, $value, $equals) {
                $current = $row->get($attribute);
                return $current > $value || ($equals && $current == $value);
            }
        );
    }

    /**
     * 当前属性小于某值
     * @param $attribute
     * @param null $value
     * @param bool $equals 是否包含等于的情况, 默认不包含
     * @return static
     */
    public function whereLt($attribute, $value, bool $equals = false)
    {
        return $this->addFilterToRows(
            function (Row $row) use ($attribute, $value, $equals) {
                $current = $row->get($attribute);
                return $current < $value || ($equals && $current == $value);
            }
        );
    }

    /**
     * 当前属性包含特定字符串
     * @param $attribute
     * @param string|null $value
     * @return static
     */
    public function whereContains($attribute, string $value)
    {
        return $this->addFilterToRows(function (Row $row) use ($attribute, $value) {
            return str_contains($row->get($attribute), $value);
        });
    }

    /**
     * 当前属性以特定字符串开头
     * @param $attribute
     * @param string|null $value
     * @return static
     */
    public function whereStartsWith($attribute, string $value)
    {
        return $this->addFilterToRows(function (Row $row) use ($attribute, $value) {
            return starts_with($row->get($attribute), $value);
        });
    }

    /**
     * 当前属性以特定字符串结尾
     * @param $attribute
     * @param string|null $value
     * @return static
     */
    public function whereEndsWith($attribute, string $value)
    {
        return $this->addFilterToRows(function (Row $row) use ($attribute, $value) {
            return ends_with($row->get($attribute), $value);
        });
    }

    /**
     * between, 两端开区间
     * @param $attribute
     * @param null $min
     * @param null $max
     * @return static
     */
    public function whereBetween($attribute, $min, $max)
    {
        return $this->addFilterToRows(function (Row $row) use ($attribute, $min, $max) {
            $current = $row->get($attribute);
            if ($current instanceof \DateTime) {
                return (new Carbon($current))->between(new Carbon($min), new Carbon($max));
            }

            return $current >= $min && $current <= $max;
        });
    }

    /**
     * 关联查询
     * @param $relation
     * @param $callback
     * @return static
     */
    public function whereHas($relation, $callback)
    {
        return $this;
    }

    private function addFilterToRows(\Closure $filter)
    {
        $this->rows = $this->rows->filter($filter);

        return $this;
    }

    /**
     * 限制条数
     * @param $limit
     * @return static
     */
    public function limit($limit)
    {
        $this->rows->slice(0, $limit);

        return $this;
    }

    /**
     * order by
     * @param $attribute
     * @param bool $desc 默认升序(false), 如需降序, 传入 true
     * @return static
     */
    public function orderBy($attribute, bool $desc = false)
    {
        $this->rows->sortBy($attribute, SORT_REGULAR, $desc);

        return $this;
    }

    /**
     * 翻页
     * @param int $perPage
     * @param string $pageName
     * @param int|null $page
     * @return static
     */
    public function paginate(int $perPage, string $pageName = 'page', int $page = null)
    {
        $this->rows->forPage($page, $perPage);

        return $this;
    }

    /**
     * 处理上方所有条件后, 执行查询语句, 返回结果集
     *
     * @param array $columns 默认获取全部字段
     * @return Collection
     */
    protected function selectQuery(array $columns = []): Collection
    {
        return $this->rows;
    }
}