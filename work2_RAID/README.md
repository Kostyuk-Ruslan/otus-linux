Linux Administrator 2020

   #####################
   #Домашнее задание 2 #
   #####################

Для выполнение домашнего задания добавил 5 дисков в вагрантфайл

<details>
<summary>Vagrantfile <code>mdadm --detail /dev/md0</code></summary>

```
# -*- mode: ruby -*-
# vim: set ft=ruby :

MACHINES = {
  :otuslinux => {
        :box_name => "centos/7",
        :ip_addr => '192.168.11.107',
<------>:disks => {
<------><------>:sata1 => {
<------><------><------>:dfile => './sata1.vdi',
<------><------><------>:size => 250,
<------><------><------>:port => 1
<------><------>},
<------><------>:sata2 => {
                        :dfile => './sata2.vdi',
                        :size => 250, # Megabytes
<------><------><------>:port => 2
<------><------>},
                :sata3 => {
                        :dfile => './sata3.vdi',
                        :size => 250,
                        :port => 3
                },
                :sata4 => {
                        :dfile => './sata4.vdi',
                        :size => 250, # Megabytes
                        :port => 4
                }

<------>}

<------><------>
  },

}

Vagrant.configure("2") do |config|

  MACHINES.each do |boxname, boxconfig|

      config.vm.define 'centos' do |box|

          box.vm.box = boxconfig[:box_name]
          box.vm.host_name = boxname.to_s

          #box.vm.network "forwarded_port", guest: 3260, host: 3260+offset

          box.vm.network "private_network", ip: boxconfig[:ip_addr]

          box.vm.provider :virtualbox do |vb|
            <-->  vb.customize ["modifyvm", :id, "--memory", "3048"]
                  needsController = false
<------><------>  boxconfig[:disks].each do |dname, dconf|
<------><------><------>  unless File.exist?(dconf[:dfile])
<------><------><------><------>vb.customize ['createhd', '--filename', dconf[:dfile], '--variant', 'Fixed', '--size', dconf[:size]]
                                needsController =  true
                          end

<------><------>  end
                  if needsController == true
                     vb.customize ["storagectl", :id, "--name", "SATA", "--add", "sata" ]
                     boxconfig[:disks].each do |dname, dconf|
                         vb.customize ['storageattach', :id,  '--storagectl', 'SATA', '--port', dconf[:port], '--device', 0, '--type', 'hdd', '--medium', dconf[:d
                     end
                  end
          end
         box.vm.provision "ansible" do |ansible|
            ansible.playbook = "playbook.yml"
       end
.......
       end
.......
    end
....
    end

</details>

```

#  В итоге при поднятии вагранта vagrant up получилась следующая разметка




<summary>Команда  <code>lsblk>

```


# Диски /dev/sdb, /dev/sdd  /dev/sde  - бдуем делать RAID10  /dev/sdf - для создания gpt раздела и 5 партиций ( задание из Д.З.)


<code>mdadm --create /dev/md0 --level=10 --raid-devices=4 /dev/sd[b-e]</code> - Добавляем диски и создаем RAID 10


