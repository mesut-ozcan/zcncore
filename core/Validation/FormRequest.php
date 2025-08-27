<?php
namespace Core\Validation;

use Core\Request;

abstract class FormRequest
{
    protected Request $request;
    protected array $validated = [];
    protected array $errors = [];

    public function __construct(?Request $request = null)
    {
        $this->request = $request ?: Request::current() ?: Request::capture();
    }

    /** Kullanıcı yetkili mi? */
    public function authorize(): bool { return true; }

    /** Doğrulama kuralları (Validator.hpp’teki gibi) */
    abstract public function rules(): array;

    /** Özel hata mesajları (opsiyonel) */
    public function messages(): array { return []; }

    public function fails(): bool
    {
        if (!$this->authorize()) {
            $this->errors = ['_auth' => 'Bu işlemi yapmaya yetkiniz yok.'];
            return true;
        }
        $res = Validator::make($this->request->all(), $this->rules());
        if (!$res['valid']) {
            // messages override
            $msgs = $this->messages();
            foreach ($res['errors'] as $k=>$v) {
                $res['errors'][$k] = $msgs[$k] ?? $v;
            }
            $this->errors = $res['errors'];
            $this->validated = [];
            return true;
        }
        $this->validated = $res['data'];
        $this->errors = [];
        return false;
    }

    public function validated(): array { return $this->validated; }
    public function errors(): array { return $this->errors; }
}
