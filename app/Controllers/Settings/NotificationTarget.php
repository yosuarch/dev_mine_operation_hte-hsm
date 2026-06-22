<?php

namespace App\Controllers\Settings;

use App\Controllers\BaseController;

class NotificationTarget extends BaseController
{
    public function index()
    {
        $db         = \Config\Database::connect();
        $recipients = $db->table('notification_target')
            ->where('deleted_at IS NULL', null, false)
            ->orderBy('channel')
            ->orderBy('name')
            ->get()->getResultArray();

        return view('pages/notification_target/index', [
            'pageTitle'  => 'Notification Recipients',
            'recipients' => $recipients,
        ]);
    }

    public function store()
    {
        $json     = $this->request->getJSON(true);
        $name     = trim($json['name']      ?? '');
        $channel  = trim($json['channel']   ?? '');
        $contact  = trim($json['contact']   ?? '');
        $notifyOn = trim($json['notify_on'] ?? 'always');

        if (!$name || !in_array($channel, ['email', 'whatsapp'], true) || !$contact) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Name, channel, and contact are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        if (!in_array($notifyOn, ['always', 'abnormal_only'], true)) {
            $notifyOn = 'always';
        }

        $db = \Config\Database::connect();
        $db->table('notification_target')->insert([
            'name'      => $name,
            'channel'   => $channel,
            'contact'   => $contact,
            'notify_on' => $notifyOn,
            'active'    => 1,
        ]);

        return $this->response->setJSON([
            'status'    => 'ok',
            'idx'       => $db->insertID(),
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function update(int $idx)
    {
        $json     = $this->request->getJSON(true);
        $name     = trim($json['name']      ?? '');
        $channel  = trim($json['channel']   ?? '');
        $contact  = trim($json['contact']   ?? '');
        $notifyOn = trim($json['notify_on'] ?? 'always');

        if (!$name || !in_array($channel, ['email', 'whatsapp'], true) || !$contact) {
            return $this->response->setStatusCode(422)->setJSON([
                'status'    => 'error',
                'message'   => 'Name, channel, and contact are required.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        if (!in_array($notifyOn, ['always', 'abnormal_only'], true)) {
            $notifyOn = 'always';
        }

        \Config\Database::connect()
            ->table('notification_target')
            ->where('idx', $idx)
            ->where('deleted_at IS NULL', null, false)
            ->update([
                'name'      => $name,
                'channel'   => $channel,
                'contact'   => $contact,
                'notify_on' => $notifyOn,
            ]);

        return $this->response->setJSON([
            'status'    => 'ok',
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function toggle(int $idx)
    {
        $db  = \Config\Database::connect();
        $row = $db->table('notification_target')
            ->where('idx', $idx)
            ->where('deleted_at IS NULL', null, false)
            ->get()->getRow();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'    => 'error',
                'message'   => 'Recipient not found.',
                'csrf_hash' => csrf_hash(),
            ]);
        }

        $newActive = $row->active ? 0 : 1;
        $db->table('notification_target')->where('idx', $idx)->update(['active' => $newActive]);

        return $this->response->setJSON([
            'status'    => 'ok',
            'active'    => $newActive,
            'csrf_hash' => csrf_hash(),
        ]);
    }

    public function destroy(int $idx)
    {
        \Config\Database::connect()
            ->table('notification_target')
            ->where('idx', $idx)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'status'    => 'ok',
            'csrf_hash' => csrf_hash(),
        ]);
    }
}
