# Changelog

All notable changes to `image-cacher` will be documented in this file

## 4.0.3

- Add speed parameter [f30f689c](https://github.com/gnahotelsolutions/image-cacher/commit/f30f689ce087450c6ccb9b587ba4d8d6b87744d5)

## 4.0.2

- Force keep transparency [912ec83f](https://github.com/gnahotelsolutions/image-cacher/commit/912ec83f291d417192c69c1a1fb03442b1646f74)
- Remove custom commands to try to reduce server load  [7eb6c986](https://github.com/gnahotelsolutions/image-cacher/commit/7eb6c98600936879a439d5f791efb2b4a5067fee)

## 4.0.1

- Check also uppercase jpg format at isJpeg function [5df38010](https://github.com/gnahotelsolutions/image-cacher/commit/5df38010715e0ba4985ed393923b7ae3e518e6ba)
- Lowercase format to avoid mistake errors [cdbbf62e](https://github.com/gnahotelsolutions/image-cacher/commit/cdbbf62ed89d7577389b20f20987a39513affc85)

## 4.0.0

- Refactor Cacher to be able to use different managers [cd2f1f76](https://github.com/gnahotelsolutions/image-cacher/commit/cd2f1f764bc004a8819f3436d424a426d8b28966)
- Add image magick and gd image managers [a27cd56c](https://github.com/gnahotelsolutions/image-cacher/commit/a27cd56ce524d7acce26c945193151da0c59163b)

## 3.0.4

- Maintain aspect ratio when requested size is zero [c7ba7ab](https://github.com/gnahotelsolutions/image-cacher/commit/c7ba7abb10098b0dfdefa1d393a2d6cf50835fba)

## 3.0.3

- Add sharpen as parameter [e781a23f](https://github.com/gnahotelsolutions/image-cacher/commit/e781a23f4a37aac728bcc3c69142855731bdf54a)

## 3.0.0

- Upgraded minimum PHP required version from 7.3 to 8.2 and tests to use format according with PHP >8.2 version [9c15a5c4](https://github.com/gnahotelsolutions/image-cacher/commit/9c15a5c4828acb4af8f81da6dbe2d7993a9b9961)

## 2.6.0

- Improve JPEG and WebP optimization during image cropping [43b964bf](https://github.com/gnahotelsolutions/image-cacher/commit/43b964bf48a245bdf2a17a4b77835312ccbbbcd8)

## 2.5.0

- Fix Laravel singleton declaration [ac7f1e74](https://github.com/gnahotelsolutions/image-cacher/commit/ac7f1e744340280a58482f7d0d608b1490f718b3)

## 2.4.0

- Option to select image quality with 80 as default [44c54223](https://github.com/gnahotelsolutions/image-cacher/commit/44c54223f49ebdf535bbd8df015161e3b5b6fc61)

## 2.3.0

- Add sharpen filter to improve image quality [a48492c](https://github.com/gnahotelsolutions/image-cacher/commit/a48492cb7c4030c3b54964c398c99103b413cf06)

## 2.2.0

- Add webp improvements [8fead5a](https://github.com/gnahotelsolutions/image-cacher/commit/8fead5aa121c25cd1362f50ee7eb5ebdce4deaf0)

## 2.0.0

- Added method to transform images into `webp` format [1bd7cb7](https://github.com/gnahotelsolutions/image-cacher/commit/1bd7cb77e1413182389ca6b87c73c6cfd4d1a7f6)
- Added classes `Manipulator` and `Format` to improve code readability [6e7c5da](https://github.com/gnahotelsolutions/image-cacher/commit/6e7c5da6363cd0c76c70c505b062454ca08d1b1e)
- Upgraded minimum PHP required version from 7.1 to 7.3 [4907433](https://github.com/gnahotelsolutions/image-cacher/commit/4907433a8add39a1744da390117b41f489a96670)

## 1.2.0

- Add method to return the image content [867828a](https://github.com/gnahotelsolutions/image-cacher/commit/867828ad48f5c7979cdef58e79cb4e2fd624290b)

## 1.1.0

- Override the previously cached thumbnail if the image is newer [810b060](https://github.com/gnahotelsolutions/image-cacher/commit/810b0600e558378f5adb98313d275333c6995da4)

## 1.0.0 - 2020-02-20

- initial release
