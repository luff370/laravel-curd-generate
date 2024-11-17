<?php

namespace Peter\LaravelBuilder\Console;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ResourceGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var string
     */
    public $langFile = 'app';

    /**
     * @var modelName
     */
    protected $modelName;

    protected $commonFields = [
        'id',
        'create_time',
        'update_time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * @var array
     */
    protected $formats = [
        'checkbox' => "\$f[] = Form::checkbox('{field}', '{title}', \$info['{field}'] ?? {defaultValue})->options({options});",
        'radio' => "\$f[] = Form::radio('{field}', '{title}', \$info['{field}'] ?? {defaultValue})->options({options});",
        'select' => "\$f[] = Form::select('{field}', '{title}', \$info['{field}'] ?? {defaultValue})->options({options})->filterable(true)->requiredNum();",
        'selectMultiple' => "\$f[] = Form::selectMultiple('{field}', '{title}', \$info['{field}'] ?? {defaultValue})->options({options})->filterable(true)->requiredNum();",
        'text' => "\$f[] = Form::text('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'password' => "\$f[] = Form::password('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'textarea' => "\$f[] = Form::textarea('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'url' => "\$f[] = Form::url('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'email' => "\$f[] = Form::email('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'color' => "\$f[] = Form::color('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'idate' => "\$f[] = Form::idate('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'number' => "\$f[] = Form::number('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'group' => "\$f[] = Form::group('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'date' => "\$f[] = Form::date('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'dateMultiple' => "\$f[] = Form::dateMultiple('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'dateTime' => "\$f[] = Form::dateTime('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'year' => "\$f[] = Form::year('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'month' => "\$f[] = Form::month('{field}', '{title}', \$info['{field}'] ?? {defaultValue});",
        'upload' => "\$f[] = Form::upload('{field}', '{title}', {action}, \$info['{field}'] ?? '')->headers(['Authori-Zation' => request()->header(config('cookie.token_name', 'Authori-zation'))]);",
        'uploadFiles' => "\$f[] = Form::uploadFiles('{field}', '{title}', {action}, \$info['{field}'] ?? [])->headers(['Authori-Zation' => request()->header(config('cookie.token_name', 'Authori-zation'))]);",
        'uploadImage' => "\$f[] = Form::uploadImage('{field}', '{title}', {action}, \$info['{field}'] ?? ''})->headers(['Authori-Zation' => request()->header(config('cookie.token_name', 'Authori-zation'))]);",
        'uploadImages' => "\$f[] = Form::uploadImages('{field}', '{title}', {action}, \$info['{field}'] ?? []})->headers(['Authori-Zation' => request()->header(config('cookie.token_name', 'Authori-zation'))]);",
        'frameImage' => "\$f[] = Form::frameImage('{field}', '{title}', {action}, \$info['{field}'] ?? ''})->width('950px')->height('560px');",
        'frameImages' => "\$f[] = Form::frameImages('{field}', '{title}', {action}, \$info['{field}'] ?? []})->width('950px')->height('560px');",
        'filter_field' => "['%s', '%s']",
        'save_column' => "['%s', '%s']",
    ];

    /**
     * @var array
     */
    private $doctrineTypeMapping = [
        'string' => [
            'enum',
            'geometry',
            'geometrycollection',
            'linestring',
            'polygon',
            'multilinestring',
            'multipoint',
            'multipolygon',
            'point',
        ],
    ];

    /**
     * @var array
     */
    protected $textFieldTypeMapping = [
        'select' => 'type|_type',
        'email' => 'email|mail',
        'password' => 'password|pwd',
        'url' => 'url|link|src|href',
        'frameImage' => 'image|img|avatar|pic|picture|cover',
        'upload' => 'file|attachment',
        'textarea' => 'remark|intro|desc|content|message|reply_content|address|description',
    ];

    protected $intFieldTypeMapping = [
        'select' => '_id|pid',
    ];

    protected $arrayFieldTypeMapping = [
        'uploadImages' => 'images|pics|pictures',
        'uploadFiles' => 'files|attachments|attrs',
        'selectMultiple' => 'ids',
        'checkbox' => 'type',
    ];

    /**
     * ResourceGenerator constructor.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $this->getModel($model);
        $this->modelName = Str::snake(class_basename($model));
    }

    public function getModelName()
    {
        return $this->modelName;
    }

    /**
     * @param mixed $model
     *
     * @return mixed
     */
    protected function getModel($model)
    {
        if ($model instanceof Model) {
            return $model;
        }

        if (!class_exists($model) || !is_string($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model [$model] !");
        }

        return new $model;
    }

    public function getFieldTypeAndDefaultValue($type, $name, $default = '')
    {
        $defaultValue = "'{$default}'";
        switch ($type) {
            case 'boolean':
            case 'bool':
            case 'tinyint':
                $fieldType = 'radio';
                break;
            case 'json':
            case 'array':
            case 'object':
                $fieldType = 'group';
                foreach ($this->arrayFieldTypeMapping as $type => $regex) {
                    if (preg_match("/($regex)$/i", $name) !== 0) {
                        $fieldType = $type;
                        break;
                    }
                }
                break;
            case 'string':
                $fieldType = 'text';
                foreach ($this->textFieldTypeMapping as $type => $regex) {
                    if (preg_match("/($regex)$/i", $name) !== 0) {
                        $fieldType = $type;
                        break;
                    }
                }
                $defaultValue = "'{$default}'";
                break;
            case 'integer':
            case 'bigint':
                $fieldType = 'number';
                foreach ($this->intFieldTypeMapping as $type => $regex) {
                    if (preg_match("/($regex)$/i", $name) !== 0) {
                        $fieldType = $type;
                        break;
                    }
                }
                break;
            case 'smallint':
            case 'decimal':
            case 'float':
            case 'real':
                $fieldType = 'number';
                break;
            case 'datetime':
            case 'timestamp':
                $fieldType = 'dateTime';
                $defaultValue = "date('Y-m-d H:i:s')";
                break;
            case 'date':
                $fieldType = 'date';
                $defaultValue = "date('Y-m-d')";
                break;
            case 'time':
                $fieldType = 'time';
                $defaultValue = "date('H:i:s')";
                break;
            case 'text':
            case 'blob':
                $fieldType = 'textarea';
                break;
            default:
                $fieldType = 'text';
        }

        if (is_numeric($defaultValue) && intval($defaultValue) == $defaultValue) {
            $defaultValue = intval($defaultValue);
        }

        return [$fieldType, $defaultValue];
    }

    /**
     * @return string
     */
    public function generateForm()
    {
        $reservedColumns = $this->getReservedColumns();
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $title = $column->getComment() ?? $name;
            $type = $column->getType()->getName();
            $default = $column->getDefault();

            // set column fieldType and defaultValue
            [$fieldType, $defaultValue] = $this->getFieldTypeAndDefaultValue($type, $name, $default);

            $action = "config('admin.url').'/admin/file/upload'";
            if (in_array($fieldType, ['frameImage'])) {
                $action = "'/admin/widget.images/index.html?fodder=image'";
            }

            $options = '';
            if (in_array($fieldType, ['radio', 'checkbox', 'select', 'selectMultiple']) && $this->getOptionsByTitle($title)) {
                $options = "\$this->toFormSelect(trans('{$this->modelName}.{$name}_map'))";
            }

            $output .= str_replace(['{field}', '{title}', '{action}', '{defaultValue}', '{options}'], [$name, $this->filterColumnTitle($title), $action, $defaultValue, $options], $this->formats[$fieldType]);

            $output .= "\r\n";
        }

        return $output;
    }

    public function generateTidyList(): string
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $key = $column->getName();
            $title = $column->getComment() ?? $key;
            $name = preg_replace(['/_id$/', '/type$/', '/status$/'], ['_name', 'type_name', 'status_name'], $key);

            $options = $this->getOptionsByTitle($title);
            if ($name != $key || $options) {
                if ($options) {
                    $output .= "\$item['{$name}'] = trans('{$this->getModelName()}.{$key}_map')[\$item['{$key}']] ?? '';";
                } else {
                    $output .= "\$item['{$name}'] = '';";
                }

                $output .= "\r\n";
            }
        }

        return $output;
    }

    /**
     * 生成vue接口API方法
     *
     * @param $title
     * @param $moduleName
     * @param $modelName
     * @param $modelSnakeName
     *
     * @return string
     */
    public function generateVueApi($title, $moduleName, $modelName, $modelSnakeName): string
    {
        return <<<EOL

/*-----------------{$title}API-------------------*/
/**
 * @description {$title} -- 列表
 * @param {Object} param params {Object} 传值参数
 */
export function {$modelName}ListApi(params) {
  return request({
    url: '{$moduleName}/{$modelSnakeName}',
    method: 'get',
    params,
  });
}

/**
 * @description {$title} -- 新增表单
 */
export function {$modelName}AddApi(params) {
  return request({
    url: '{$moduleName}/{$modelSnakeName}/create',
    method: 'get',
    params
  });
}

/**
 * @description {$title} -- 更新表单
 */
export function {$modelName}EditApi(id) {
  return request({
    url: `{$moduleName}/{$modelSnakeName}/\${id}/edit`,
    method: 'get',
  });
}

/**
 * @description {$title} -- 更新指定字段值
 */
export function {$modelName}SetFieldApi(id,value,field) {
  return request({
    url: `{$moduleName}/{$modelSnakeName}/set_field_value/\${id}/\${value}/\${field}`,
    method: 'put',
  });
}

EOL;
    }

    /**
     * 生成VUE路由方法
     *
     * @param $title
     * @param $moduleName
     * @param $modelSnakeName
     *
     * @return string
     */
    public function generateVueRouter($title, $moduleName, $modelSnakeName)
    {
        return <<<EOF
{
  path: '{$modelSnakeName}/index/:id?',
  name: `\${pre}{$modelSnakeName}`,
  meta: {
    auth: ['{$moduleName}-{$modelSnakeName}'],
    title: '{$title}',
    keepAlive: true,
  },
  component: () => import('@/pages/{$moduleName}/{$modelSnakeName}/index'),
},
EOF;
    }

    public function filterColumnTitle($title): string
    {
        $pos = mb_strpos($title, '(');
        if ($pos !== false) {
            $title = mb_substr($title, 0, $pos);
        }
        $title = str_replace(['ID', 'id'], '', $title);

        return trim($title);
    }

    public function getOptionsByTitle($title): array
    {
        $pos = mb_strpos($title, '(');
        if ($pos === false) {
            return [];
        }

        $title = mb_substr($title, $pos + 1, -1);
        $arr = explode('，', $title);
        if (count($arr) == 1) {
            $arr = explode(',', $title);
        }

        $options = [];
        foreach ($arr as $item) {
            $item = trim($item);
            $pos = mb_strpos($item, '-');
            if ($pos !== false) {
                $items = explode('-', $item);
                $options[$items[0]] = $items[1];
            } else {
                $options[$item] = $item;
            }
        }

        return $options;
    }

    public function generateFilterFields()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            $title = $column->getComment() ?? $name;

            if (
                str_contains($name, '_id')
                || str_contains($name, 'type')
                || str_contains($name, 'status')
                || ($this->getOptionsByTitle($title))
            ) {
                $output .= sprintf($this->formats['filter_field'], $name, '');
                $output .= ",\r\n";
            }
        }
        $output .= sprintf($this->formats['filter_field'], 'keyword', '');
        $output .= ",\r\n";
        $output .= sprintf($this->formats['filter_field'], 'time', '');
        $output .= ",\r\n";

        return $output;
    }

    public function generateSaveFields()
    {
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            $defaultValue = $column->getDefault() ?? '';
            if (in_array($name, $this->getReservedColumns())) {
                continue;
            }

            $output .= sprintf($this->formats['save_column'], $name, $defaultValue);
            $output .= ",\r\n";
        }

        return $output;
    }

    protected function getReservedColumns()
    {
        return [
            $this->model->getKeyName(),
            $this->model->getCreatedAtColumn(),
            $this->model->getUpdatedAtColumn(),
            'token',
            'uuid',
            'is_del',
            'reg_time',
            'last_time',
            'reg_ip',
            'last_ip',
            'add_time',
            'deleted_at',
            'app_platform',
            'app_version',
            'terminal',
            'os_version',
            'platform',
        ];
    }

    public function generateLang(): string
    {
        $output = '';
        $columnMap = '';

        foreach ($this->getTableColumns() as $column) {
            $key = $column->getName();
            $title = $column->getComment() ?? $key;

            $name = $this->filterColumnTitle($title);
            if (!in_array($key, $this->commonFields)) {
                $output .= "\t\t'{$key}' => '{$name}'";
                $output .= ",\r\n";

                $options = $this->getOptionsByTitle($title);
                if (count($options) > 0) {
                    $columnMap .= "\t'{$key}_map' => [\r\n";
                    foreach ($options as $key => $val) {
                        $columnMap .= "\t\t'{$key}' => '{$val}',\r\n";
                    }
                    $columnMap .= "\t],\r\n";
                }
            }
        }

        $outputFile = <<<PHP
<?php

return [
    'attributes'=>[
$output
    ],

$columnMap
];
PHP;

        return $outputFile;
    }

    public function generateVueColumn(): string
    {
        $output = '';

        $columnFilter = [
            'json',
            'array',
            'object',
            'text',
        ];

        $fieldFilter = [
            'updated_at',
            'update_time',
        ];

        foreach ($this->getTableColumns() as $column) {
            $key = $column->getName();
            $type = $column->getType()->getName();
            $title = $column->getComment() ?? $key;

            $name = $this->filterColumnTitle($title);
            if (in_array($name, $this->commonFields)) {
                $name = trans($this->langFile . '.attributes.' . $key);
            }
            if (!in_array($type, $columnFilter) && !in_array($key, $fieldFilter)) {
                if (str_contains($key, 'sort')) {
                    $output .= $this->generateVueColumnSort($key);
                } else {
                    $key = preg_replace(['/_id$/', '/type$/', '/status$/'], ['_name', 'type_name', 'status_name'], $key);
                    $output .= "{\r\n";
                    $output .= "\ttitle: '{$name}',\r\n";
                    $output .= "\tkey: '{$key}',\r\n";
                    $output .= "\tminWidth: 100,\r\n";
                    $output .= "},\r\n";
                }
            }
        }

        return $output;
    }

    public function generateVueFilterField(): string
    {
        $reservedColumns = $this->getReservedColumns();
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $title = $column->getComment() ?? $name;

            if ($this->getOptionsByTitle($title)) {
                $output .= "{$name}: '',";
                $output .= "\r\n";
            }
        }

        return $output;
    }

    public function generateVueFilterForm(): string
    {
        $reservedColumns = $this->getReservedColumns();
        $output = '';

        foreach ($this->getTableColumns() as $column) {
            $name = $column->getName();
            if (in_array($name, $reservedColumns)) {
                continue;
            }
            $title = $column->getComment() ?? $name;
            $type = $column->getType()->getName();
            $default = $column->getDefault();

            // set column fieldType and defaultValue
            [$fieldType, $defaultValue] = $this->getFieldTypeAndDefaultValue($type, $name, $default);

            if (in_array($fieldType, ['radio', 'checkbox', 'select', 'selectMultiple']) && $options = $this->getOptionsByTitle($title)) {
                $output .= $this->generateVueSelect($name, $this->filterColumnTitle($title), $options);
                $output .= "\r\n";
            }
        }

        return $output;
    }

    protected function generateVueSelect($field, $title, $options)
    {
        $option = '';
        foreach ($options as $val => $name) {
            $option .= "\r\n\t\t<Option value='{$val}'>{$name}</Option>";
        }

        return <<<EOF
  <Col v-bind="grid">
            <FormItem label="{$title}：" label-for="status">
              <Select v-model="filter.{$field}" placeholder="请选择" element-id="{$field}" clearable @on-change="search">
                <Option value="">全部</Option>{$option}
              </Select>
            </FormItem>
          </Col>
EOF;
    }

    protected function generateVueSlot($field)
    {
        return <<<EOF
  <template slot-scope="{ row, index }" slot="{$field}">
          <i-switch
            v-model="row.{$field}"
            :value="row.{$field}"
            :true-value="1"
            :false-value="0"
            @on-change="setFieldData(row.id,row.{$field},'{$field}')"
            size="large"
          >
            <span slot="open">启用</span>
            <span slot="close">停用</span>
          </i-switch>
        </template>
EOF;
    }

    protected function generateVueColumnSort($field)
    {
        return <<<EOF
        {
          title: '排序',
          key: '{$field}',
          minWidth: 100,
          render: (h, params) => {
            return h('div', [
              h('InputNumber', {
                props: {
                  min: 0,
                  precision: 0,
                  value: params.row.{$field},
                },
                on: {
                  'on-change': (e) => {
                    debounce(() => {
                      console.log('{$field}值改变了', e, params);
                      this.setFieldData(params.row.id, e, '{$field}');
                    }, 1000);
                  },
                },
              }),
            ]);
          },
        },
EOF;
    }

    /**
     * Get columns of a giving model.
     *
     * @return \Doctrine\DBAL\Schema\Column[]
     * @throws \Exception
     *
     */
    protected function getTableColumns()
    {
        if (!$this->model->getConnection()->isDoctrineAvailable()) {
            throw new \Exception(
                'You need to require doctrine/dbal: ~2.3 in your own composer.json to get database columns. '
            );
        }

        $table = $this->model->getConnection()->getTablePrefix() . $this->model->getTable();
        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schema */
        $schema = $this->model->getConnection()->getDoctrineSchemaManager($table);

        // custom mapping the types that doctrine/dbal does not support
        $databasePlatform = $schema->getDatabasePlatform();

        foreach ($this->doctrineTypeMapping as $doctrineType => $dbTypes) {
            foreach ($dbTypes as $dbType) {
                $databasePlatform->registerDoctrineTypeMapping($dbType, $doctrineType);
            }
        }

        $database = null;
        if (strpos($table, '.')) {
            [$database, $table] = explode('.', $table);
        }

        return $schema->listTableColumns($table, $database);
    }

    /**
     * Format label.
     *
     * @param string $value
     *
     * @return string
     */
    protected function formatLabel($value)
    {
        //return ucfirst(str_replace(['-', '_'], ' ', $value));

        $commonFields = [
            'id',
            'created_at',
            'updated_at',
        ];

        return in_array($value, $commonFields) ? "trans('" . $this->langFile . ".attributes." . $value . "')"
            : "trans('" . $this->modelName . ".attributes." . $value . "')";
    }
}
