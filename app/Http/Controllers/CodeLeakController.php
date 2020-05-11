<?php

namespace App\Http\Controllers;

use App\Models\CodeLeak;
use Illuminate\Http\Request;

class CodeLeakController extends Controller
{
    public function view()
    {
        $data = ['title' => '扫描结果'];
        return view('codeLeak/index')->with($data);
    }

    /**
     * 扫描结果列表
     *
     * @param  Request  $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('limit', 10);
        $query = CodeLeak::query();
        $query->when($request->input('keyword'), function ($query, $keyword) {
            return $query->where('keyword', $keyword);
        });
        $query->when($request->input('repo_name'), function ($query, $repoName) {
            return $query->where('repo_name', 'like', "%$repoName%");
        });
        $query->when($request->input('repo_owner'), function ($query, $repoOwner) {
            return $query->where('repo_owner', 'like', "%$repoOwner%");
        });
        $query->when($request->filled('status'), function ($query) use ($request) {
            return $query->where('status', $request->input('status'));
        });
        $query->when($request->input('path'), function ($query, $path) {
            return $query->where('path', 'like', "%$path%");
        });
        $query->when($request->input('sdate'), function ($query, $sdate) {
            return $query->where('created_at', '>=', date('Y-m-d 00:00:00', strtotime($sdate)));
        });
        $query->when($request->input('edate'), function ($query, $edate) {
            return $query->where('created_at', '<=', date('Y-m-d 23:59:59', strtotime($edate)));
        });
        return $query->orderBy('created_at', 'desc')->paginate($perPage, '*', 'page', $page);
    }

    /**
     * 更新数据
     *
     * @param  Request  $request
     * @param $id
     * @return array
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        try {
            $this->validate($input, $this->_rules(), $this->_messages());
            $success = CodeLeak::find($id)->update($input);
        } catch (\Exception $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
        return ['success' => $success];
    }

    /**
     * 规则定义
     *
     * @return array
     */
    private function _rules()
    {
        return [
            'status' => 'int',
            'description' => 'string|max:255',
        ];
    }

    /**
     * 错误信息返回
     *
     * @return array
     */
    private function _messages()
    {
        return [
            'status.type' => 'status type error',
        ];
    }
}
