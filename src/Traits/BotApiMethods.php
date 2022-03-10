<?php

namespace Telegram\Traits;

trait BotApiMethods
{
    public function mappingParameters(array $parameters = [], array|string|null $keyboard = null, array $extra = []): array
    {
        $parameters = array_merge($parameters, $extra);

        if ($keyboard && isset($this->keyboard)) {
            $parameters['reply_markup'] = is_array($keyboard) ? $this->keyboard->show($keyboard) : $keyboard;
        }

        if (isset($this->config)) {
            $parameters['parse_mode'] = $this->config('telegram.parse_mode', 'html');
        }

        if (!empty($parameters['text'])) {
            $parameters['text'] = implode("\n", array_map('trim', explode("\n", $parameters['text'])));
            $parameters['text'] = str_replace('<<<', '«', $parameters['text']);
            $parameters['text'] = str_replace('>>>', '»', $parameters['text']);
        }

        if (!empty($parameters['caption'])) {
            $parameters['caption'] = implode("\n", array_map('trim', explode("\n", $parameters['caption'])));
            $parameters['caption'] = str_replace('<<<', '«', $parameters['caption']);
            $parameters['caption'] = str_replace('>>>', '»', $parameters['caption']);
        }

        return $parameters;
    }

    /**
     * @see https://core.telegram.org/bots/api#getupdates
     */
    public function getUpdates(int $offset = 0, int $limit = 100, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'offset' => $offset,
            'limit' => $limit,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getme
     */
    public function getMe()
    {
        $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#setwebhook
     */
    public function setWebhook(string $url, array $extra = [])
    {
        return $this->method(__FUNCTION__, array_merge([
            'url' => $url,
            'max_connections' => 100,
        ], $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletewebhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false)
    {
        return $this->method(__FUNCTION__, [
            'drop_pending_updates' => $dropPendingUpdates,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getwebhookinfo
     */
    public function getWebhookInfo()
    {
        return $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#logout
     */
    public function logOut()
    {
        return $this->method(__FUNCTION__);
    }

    /**
     * @see https://core.telegram.org/bots/api#close
     */
    public function close()
    {
        return $this->method(__FUNCTION__);
    }

    /**
     * Possible `$action` values (default `typing`):
     * - `typing` for text messages,
     * - `upload_photo` for photos,
     * - `record_video` or `upload_video` for videos,
     * - `record_voice` or `upload_voice` for voice notes,
     * - `upload_document` for general files,
     * - `choose_sticker` for stickers,
     * - `find_location` for location data,
     * - `record_video_note` or `upload_video_note` for video notes.
     *
     * @see https://core.telegram.org/bots/api#sendchataction
     */
    public function sendChatAction(string|int $chatId, string $action = 'typing')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'action' => $action,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendmessage
     */
    public function sendMessage(string|int $chatId, string $text, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#forwardmessage
     */
    public function forwardMessage(string|int $chatId, string|int $fromChatId, string|int $messageId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#copymessage
     */
    public function copyMessage(string|int $fromChatId, string|int $toChatId, string|int $messageId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $toChatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#sendpoll
     */
    public function sendPoll(string|int $chatId, string $question, array $options, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => json_encode($options),
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendphoto
     */
    public function sendPhoto(string|int $chatId, string|InputFile $photo, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'photo' => $photo,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendaudio
     */
    public function sendAudio(string|int $chatId, string|InputFile $audio, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'audio' => $audio,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#senddocument
     */
    public function sendDocument(string|int $chatId, string|InputFile $document, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'document' => $document,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendanimation
     */
    public function sendAnimation(string|int $chatId, string|InputFile $animation, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'animation' => $animation,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvideo
     */
    public function sendVideo(string|int $chatId, string|InputFile $video, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'video' => $video,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvideonote
     */
    public function sendVideoNote(string|int $chatId, string|InputFile $videoNote, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'video_note' => $videoNote,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendsticker
     */
    public function sendSticker(string|int $chatId, string|InputFile $sticker, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendvoice
     */
    public function sendVoice(string|int $chatId, string|InputFile $voice, string $caption = '', array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'caption' => $caption,
            'voice' => $voice,
        ], $keyboard, $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendmediagroup
     */
    public function sendMediaGroup(string|int $chatId, array $media, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'media' => json_encode($media),
        ], extra: $extra), true);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendlocation
     */
    public function sendLocation(string|int $chatId, int|float $latitude, int|float $longitude, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $keyboard, $extra));
    }

    /**
     * Possible `$emoji` values (default `$emoji = 🎲`):
     * - `🎲`, `dice`
     * - `🎯`, `darts`
     * - `🏀`, `basketball`
     * - `⚽️`, `football`
     * - `🎰`, `777`, `slots`
     *
     * @see https://core.telegram.org/bots/api#senddice
     */
    public function sendDice(string|int $chatId, string $emoji = '🎲', array|string|null $keyboard = null, array $extra = [])
    {
        $emoji = str_ireplace(['dice'], '🎲', $emoji);
        $emoji = str_ireplace(['darts'], '🎯', $emoji);
        $emoji = str_ireplace(['basketball'], '🏀', $emoji);
        $emoji = str_ireplace(['football'], '⚽️', $emoji);
        $emoji = str_ireplace(['777', 'slots'], '🎰', $emoji);

        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'emoji' => $emoji,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getuserprofilephotos
     */
    public function getUserProfilePhotos(string|int $userId, int $offset = 0, int $limit = 100)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'offset' => $offset,
            'limit' => $limit,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getfile
     */
    public function getFile(string $fileId)
    {
        return $this->method(__FUNCTION__, [
            'file_id' => $fileId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#banchatmember
     */
    public function banChatMember(string|int $chatId, string|int $userId, int $untilDate, bool $revokeMessages = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'until_date' => $untilDate,
            'revoke_messages' => $revokeMessages,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unbanchatmember
     */
    public function unbanChatMember(string|int $chatId, string|int $userId, bool $onlyIfBanned = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => $onlyIfBanned,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#restrictchatmember
     */
    public function restrictChatMember(string|int $chatId, string|int $userId, int $untilDate, array $permissions = [])
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => json_encode($permissions),
            'until_date' => $untilDate,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#promotechatmember
     */
    public function promoteChatMember(string|int $chatId, string|int $userId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatadministratorcustomtitle
     */
    public function setChatAdministratorCustomTitle(string|int $chatId, string|int $userId, string $title = '')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'custom_title' => $title,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatpermissions
     */
    public function setChatPermissions(string|int $chatId, array $permissions)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'permissions' => json_encode($permissions),
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#exportchatinvitelink
     */
    public function exportChatInviteLink(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#createchatinvitelink
     */
    public function createChatInviteLink(string|int $chatId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editchatinvitelink
     */
    public function editChatInviteLink(string|int $chatId, string $inviteLink, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#revokechatinvitelink
     */
    public function revokeChatInviteLink(string|int $chatId, string $inviteLink)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#approvechatjoinrequest
     */
    public function approveChatJoinRequest(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#declinechatjoinrequest
     */
    public function declineChatJoinRequest(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatphoto
     */
    public function setChatPhoto(string|int $chatId, InputFile $photo)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'photo' => $photo,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletechatphoto
     */
    public function deleteChatPhoto(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchattitle
     */
    public function setChatTitle(string|int $chatId, string $title)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'title' => $title,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatdescription
     */
    public function setChatDescription(string|int $chatId, string $description = '')
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'description' => $description,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#pinchatmessage
     */
    public function pinChatMessage(string|int $chatId, string|int $messageId, bool $disableNotification = false)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unpinchatmessage
     */
    public function unpinChatMessage(string|int $chatId, string|int $messageId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#unpinallchatmessages
     */
    public function unpinAllChatMessages(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#leavechat
     */
    public function leaveChat(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchat
     */
    public function getChat(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatadministrators
     */
    public function getChatAdministrators(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatmembercount
     */
    public function getChatMemberCount(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getchatmember
     */
    public function getChatMember(string|int $chatId, string|int $userId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setchatstickerset
     */
    public function setChatStickerSet(string|int $chatId, string $stickerSetName)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'sticker_set_name' => $stickerSetName,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletechatstickerset
     */
    public function deleteChatStickerSet(string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagetext
     */
    public function editMessageText(string|int $messageId, string|int $chatId, string $text, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagecaption
     */
    public function editMessageCaption(string|int $messageId, string|int $chatId, string $caption, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'caption' => $caption,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagemedia
     */
    public function editMessageMedia(string|int $messageId, string|int $chatId, array $media, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => json_encode($media),
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#editmessagereplymarkup
     */
    public function editMessageReplyMarkup(string|int $messageId, string|int $chatId, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#stoppoll
     */
    public function stopPoll(string|int $messageId, string|int $chatId, array|string|null $keyboard = null)
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $keyboard));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletemessage
     */
    public function deleteMessage(string|int $messageId, string|int $chatId)
    {
        return $this->method(__FUNCTION__, [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#getstickerset
     */
    public function getStickerSet(string $name)
    {
        return $this->method(__FUNCTION__, [
            'name' => $name,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#deletestickerfromset
     */
    public function deleteStickerFromSet(string $sticker)
    {
        return $this->method(__FUNCTION__, [
            'sticker' => $sticker,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#uploadstickerfile
     */
    public function uploadStickerFile(string|int $userId, InputFile $image)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'png_sticker' => $image,
        ], true);
    }

    /**
     * @see https://core.telegram.org/bots/api#createnewstickerset
     */
    public function createNewStickerSet(string|int $userId, string $name, string $title, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'name' => $name,
            'title' => $title,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#addstickertoset
     */
    public function addStickerToSet(string|int $userId, string $name, string $emojis, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'name' => $name,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setstickerpositioninset
     */
    public function setStickerPositionInSet(string $sticker, int $position)
    {
        return $this->method(__FUNCTION__, [
            'sticker' => $sticker,
            'position' => $position,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#setstickersetthumb
     */
    public function setStickerSetThumb(string|int $userId, string $name, InputFile|string $thumb)
    {
        return $this->method(__FUNCTION__, [
            'user_id' => $userId,
            'name' => $name,
            'thumb' => $thumb,
        ]);
    }

    /**
     * @see https://core.telegram.org/bots/api#sendgame
     */
    public function sendGame(string|int $chatId, string $name, array|string|null $keyboard = null, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'chat_id' => $chatId,
            'game_short_name' => $name,
        ], $keyboard, $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setgamescore
     */
    public function setGameScore(string|int $userId, int $score, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
            'score' => $score,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#getgamehighscores
     */
    public function getGameHighScores(string|int $userId, array $extra = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'user_id' => $userId,
        ], extra: $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#answercallbackquery
     */
    public function answerCallbackQuery(array $parameters = [])
    {
        return $this->method(__FUNCTION__, $this->mappingParameters([
            'callback_query_id' => $this->payload('callback_query.id'),
        ], extra: $parameters));
    }

    /**
     * @see https://core.telegram.org/bots/api#answerinlinequery
     */
    public function answerInlineQuery(array $results = [], array $extra = [])
    {
        return $this->method(__FUNCTION__, array_merge([
            'inline_query_id' => $this->payload('inline_query.id'),
            'results' => json_encode($results),
        ], $extra));
    }

    /**
     * @see https://core.telegram.org/bots/api#setmycommands
     */
    public function setMyCommands(array $commands, ?array $scope = null, ?string $language = null)
    {
        return $this->method(__FUNCTION__, Util::trimArray([
            'commands' => json_encode($commands),
            'scope' => $scope ? json_encode($scope) : '',
            'language_code' => $language ?? '',
        ]));
    }

    /**
     * @see https://core.telegram.org/bots/api#deletemycommands
     */
    public function deleteMyCommands(?array $scope = null, ?string $language = null)
    {
        return $this->method(__FUNCTION__, Util::trimArray([
            'scope' => $scope ? json_encode($scope) : null,
            'language_code' => $language,
        ]));
    }

    /**
     * @see https://core.telegram.org/bots/api#getmycommands
     */
    public function getMyCommands()
    {
        return $this->method(__FUNCTION__);
    }
}