<?php
namespace base\controller;
class base_html{
    public function close_btn(): string
    {
        return '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>';
    }
}
