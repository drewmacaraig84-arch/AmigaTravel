import os
import struct

def run():
    orig_path = '.git/lfs/objects/f4/af/f4af193a7a32faff970bb18f1ba7392ac068eb9d2730aa594ab1820885e057e5'
    out_path = 'public/video/Coral Reef Safari.mp4'
    temp_path = 'public/video/Coral Reef Safari_faststart_temp.mp4'

    print(f"Reading original file from {orig_path}...")
    with open(orig_path, 'rb') as f:
        f.seek(0)
        ftyp_data = f.read(32)
        assert ftyp_data[4:8] == b'ftyp', "Invalid ftyp"

        f.seek(32)
        free_data = f.read(8)
        assert free_data[4:8] == b'free', "Invalid free"

        f.seek(40)
        mdat_hdr = f.read(8)
        assert mdat_hdr[4:8] == b'mdat', "Invalid mdat"
        old_mdat_pos = 40
        mdat_size = struct.unpack('>I', mdat_hdr[:4])[0]

        f.seek(170535131)
        moov_hdr = f.read(8)
        assert moov_hdr[4:8] == b'moov', "Invalid moov"
        moov_size = struct.unpack('>I', moov_hdr[:4])[0]
        f.seek(170535131)
        moov_data = bytearray(f.read(moov_size))

    # New layout: ftyp (32 bytes) + moov (29286 bytes) + mdat (170535091 bytes)
    new_mdat_pos = len(ftyp_data) + len(moov_data)
    shift = new_mdat_pos - old_mdat_pos
    print(f"Old mdat pos: {old_mdat_pos}, New mdat pos: {new_mdat_pos}, Exact shift: +{shift}")

    # Patch all stco atoms
    offset = 0
    stco_count = 0
    while True:
        idx = moov_data.find(b'stco', offset)
        if idx == -1: break
        box_size = struct.unpack('>I', moov_data[idx-4:idx])[0]
        entry_count = struct.unpack('>I', moov_data[idx+8:idx+12])[0]
        first_old_offset = struct.unpack('>I', moov_data[idx+12:idx+16])[0]
        first_new_offset = first_old_offset + shift
        print(f"Patching stco at {idx}: {entry_count} entries. First chunk offset: {first_old_offset} -> {first_new_offset}")

        data_start = idx + 12
        for i in range(entry_count):
            e_pos = data_start + i * 4
            old_offset = struct.unpack('>I', moov_data[e_pos:e_pos+4])[0]
            new_offset = old_offset + shift
            moov_data[e_pos:e_pos+4] = struct.pack('>I', new_offset)

        stco_count += 1
        offset = idx + box_size

    print(f"Successfully patched {stco_count} stco atoms.")

    print(f"Writing to {temp_path}...")
    with open(orig_path, 'rb') as f_in, open(temp_path, 'wb') as f_out:
        f_out.write(ftyp_data)
        f_out.write(moov_data)

        f_in.seek(old_mdat_pos)
        bytes_left = mdat_size
        chunk_size = 8 * 1024 * 1024
        while bytes_left > 0:
            chunk = f_in.read(min(bytes_left, chunk_size))
            if not chunk: break
            f_out.write(chunk)
            bytes_left -= len(chunk)

    if os.path.exists(out_path):
        os.remove(out_path)
    os.replace(temp_path, out_path)
    print(f"Replacement complete! Final file size: {os.path.getsize(out_path)} bytes.")

if __name__ == '__main__':
    run()
