#ifndef LXLSX_PLATFORM_H
#define LXLSX_PLATFORM_H

/*
 * Portability shims for the libxlsx reader module. Private - keeps Windows-specific
 * headers out of the public API.
 *
 * - ssize_t: POSIX-only on Linux/macOS; on MSVC use SSIZE_T from BaseTsd.h.
 * - read / lseek wrappers: POSIX on Unix, _read / _lseeki64 on MSVC.
 * - binary fd mode: required on Windows when reading ZIP data from a caller fd.
 * - off_t: synthesised on MSVC where the type is not declared in this scope.
 */

#include <stddef.h>

#if defined(_WIN32)
#  include <fcntl.h>
#  include <io.h>
#  define lxlsx_reader_set_binary(fd) _setmode((fd), _O_BINARY)
#else
#  define lxlsx_reader_set_binary(fd) 0
#endif

#if defined(_MSC_VER)
#  include <io.h>
#  include <BaseTsd.h>
   typedef SSIZE_T ssize_t;
   typedef long long lxlsx_reader_off_t;
#  define lxlsx_reader_read(fd, buf, n)        _read((fd), (buf), (unsigned int)(n))
#  define lxlsx_reader_lseek(fd, off, whence)  _lseeki64((fd), (off), (whence))
#else
#  include <sys/types.h>
#  include <unistd.h>
   typedef off_t lxlsx_reader_off_t;
#  define lxlsx_reader_read(fd, buf, n)        read((fd), (buf), (n))
#  define lxlsx_reader_lseek(fd, off, whence)  lseek((fd), (off), (whence))
#endif

/* 64-bit ftell: plain ftell() returns long, which is 32-bit on Windows and
 * ILP32 targets and caps whole-file sizes / ZIP offsets at 2GB. Writer and
 * edit paths use this for file-size and offset queries. */
#include <stdio.h>
#if defined(_MSC_VER)
#  define lxlsx_ftello(fp) _ftelli64(fp)
#else
#  define lxlsx_ftello(fp) ftello(fp)
#endif

#endif
